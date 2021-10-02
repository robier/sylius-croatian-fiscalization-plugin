<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Cli;

use Doctrine\ORM\EntityManagerInterface;
use Robier\Fiscalization\Bill;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\Fiscalization;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\FiscalizationFailLog;
use Robier\SyliusCroatianFiscalizationPlugin\Order\InvoiceNumberGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

final class BillSequenceSetterCommand extends Command
{
    protected static $defaultName = 'robier:croatian-fiscalization:set-bill-sequence';

    public function __construct(
        private LockFactory $lockFactory,
        private EntityManagerInterface $entityManager,
        private string $billSequenceFile
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('sequence', InputArgument::REQUIRED, 'Sequence that will be set')
            ->setDescription('Sets bill sequence number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sequence = $input->getArgument('sequence');

        $identifier = Bill\Identifier::fromString($sequence);

        $lock = $this->lockFactory->createLock(InvoiceNumberGenerator::LOCK_KEY);
        $lock->acquire(true);

        if (is_readable($this->billSequenceFile) === false) {
            file_put_contents($this->billSequenceFile, '');
        }

        if ((string)$identifier === trim(file_get_contents($this->billSequenceFile))) {
            $output->writeln('<info>Identifier is identical, no change</info>');
            $lock->release();
            return 0;
        }

        $ordersWithThatSequence = count($this->entityManager
            ->getRepository(Fiscalization::class)
            ->findBy(['sequenceNumber' => (string)$sequence]));

        if ($ordersWithThatSequence !== 0) {
            $output->writeln('<error>Can not use provided sequence number as order with that sequence exists</error>');
            $lock->release();
            return 1;
        }

        $failedOrdersWithThatSequence = count($this->entityManager
            ->getRepository(FiscalizationFailLog::class)
            ->findBy(['billIdentifier' => (string)$sequence]));

        if ($failedOrdersWithThatSequence !== 0) {
            $output->writeln('<error>Can not use provided sequence number as failed orders with that sequence exists</error>');
            $lock->release();
            return 1;
        }

        file_put_contents($this->billSequenceFile, (string)$identifier);
        $lock->release();
        $output->writeln('<info>Sequence number set!</info>');
        return 0;
    }
}
