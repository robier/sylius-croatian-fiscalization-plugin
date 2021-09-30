<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\EventProducer;

use Robier\SyliusCroatianFiscalizationPlugin\Service\BillSender;
use Sylius\Component\Core\Model\PaymentInterface;

final class Fiscalize
{
    public function __construct(private BillSender $billSender, private array $disableOnPaymentCodes)
    {
        // noop
    }

    public function __invoke(PaymentInterface $payment): void
    {
        if (in_array($payment->getMethod()->getCode(), $this->disableOnPaymentCodes, true)) {
            return;
        }

        $this->billSender->new($payment->getOrder());
    }
}