<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Converter;

use DateTimeImmutable;
use Robier\Fiscalization\Bill;
use Robier\Fiscalization\Company;
use Robier\Fiscalization\Oib;
use Robier\Fiscalization\Operator;
use Robier\Fiscalization\Tax;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class BillConverter
{
    public function __construct()
    {

    }

    private function bill(): Bill
    {
        // @todo change
        $oib = new Oib('96676332840');
        $company = new Company($oib, true);
        $operator = new Operator($oib);

        $bill = new Bill(
            $company,
            $operator,
            New DateTimeImmutable(),
            new Bill\Identifier(1, 'POS1', 1),
            Bill\PaymentType::card(),
            Bill\SequenceType::billingDevice(),
            false
        );

        return $bill;
    }

    public function __invoke(OrderInterface $order): Bill
    {
        // @todo figure out how to get TAX-es out
//        $taxes = $order->getAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT);
//        $extractedTaxes = [];
//        foreach ($taxes as $tax) {
//            if (!isset($extractedTaxes[$tax->getLabel()])) {
//                $extractedTaxes[$tax->getLabel()] = 0;
//            }
//
//            $extractedTaxes[$tax->getLabel()] += $tax->getAmount();
//        }

        $bill = $this->bill();
        $bill->addTax(new Tax\Vat(100_00, 10_00));

        return $bill;
    }
}