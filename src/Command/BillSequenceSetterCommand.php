<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use Robier\Fiscalization\Bill;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\Fiscalization;
use Robier\SyliusCroatianFiscalizationPlugin\Entity\FiscalizationFailLog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

final class BillSequenceSetterCommand extends Command
{
    protected string $PATH;

    protected static $defaultName = 'robier:croatian-fiscalization:set-bill-sequence';

    public function __construct(
        private LockFactory $lockFactory,
        private EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();

        $pathToFile = __DIR__ . '/../Resources/config/bill-sequence.txt';
        $path = realpath($pathToFile);
        if ($path === false) {
            file_put_contents($pathToFile, '');
        }

        $this->PATH = realpath($pathToFile);
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

        $lock = $this->lockFactory->createLock('robier-sylius-croatian-fiscalization-plugin.fiscalize');
        $lock->acquire(true);

        if ((string)$identifier === trim(file_get_contents($this->PATH))) {
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

        file_put_contents($this->PATH, (string)$identifier);
        $lock->release();
        $output->writeln('<info>Sequence number set!</info>');
        return 0;
    }
}
