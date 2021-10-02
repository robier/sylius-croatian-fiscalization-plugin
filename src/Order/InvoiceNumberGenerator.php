<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Order;

use Robier\Fiscalization\Bill;
use Sylius\Bundle\OrderBundle\NumberGenerator\OrderNumberGeneratorInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\Lock\LockFactory;

final class InvoiceNumberGenerator implements OrderNumberGeneratorInterface
{
    public function __construct(
        private LockFactory $lockFactory,
        private string $billSequenceFile,
    )
    {
        // noop
    }

    public function generate(OrderInterface $order): string
    {
        $lock = $this->lockFactory->createLock('robier-sylius-croatian-fiscalization-plugin.generate_invoice_number');
        $lock->acquire(true);

        $identifier = Bill\Identifier::fromString(trim(file_get_contents($this->billSequenceFile)))->next();
        file_put_contents($this->billSequenceFile, (string)$identifier);

        $lock->release();

        return (string) $identifier;
    }
}
