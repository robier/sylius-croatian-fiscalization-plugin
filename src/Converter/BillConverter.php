<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Converter;

use DateTimeImmutable;
use Robier\Fiscalization\Bill;
use Robier\Fiscalization\Tax;
use Robier\SyliusCroatianFiscalizationPlugin\Factory\BillFactory;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class BillConverter
{
    public function __construct(private BillFactory $billFactory)
    {
        // noop
    }

    public function __invoke(
        OrderInterface $order,
        DateTimeImmutable $created,
        Bill\Identifier $billIdentifier,
        bool $redelivery = false
    ): Bill
    {
        $extractedTaxes = [];
        foreach ($order->getItems() as $item) {
            foreach ($item->getUnits() as $unit) {
                foreach ($unit->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT) as $tax) {
                    if (isset($extractedTaxes[$tax->getDetails()['taxRateCode']]) === false) {
                        $extractedTaxes[$tax->getDetails()['taxRateCode']] = [];
                    }
                    $extractedTaxes[$tax->getDetails()['taxRateCode']][] = [
                        $unit->getOrderItem()->getUnitPrice(),
                        (int)($tax->getDetails()['taxRateAmount'] * 10000)
                    ];
                }
            }
        }

        $bill = $this->billFactory->new($created, $billIdentifier, $redelivery);

        foreach ($extractedTaxes['PDV'] as $tax) {
            $bill->addTax(new Tax\Vat(...$tax));
        }

        $shippingAmount = 0;
        foreach ($order->getAdjustmentsRecursively(AdjustmentInterface::SHIPPING_ADJUSTMENT) as $shipping) {
            $shippingAmount += $shipping->getAmount();
        }

        if ($shippingAmount !== 0) {
            $bill->setTaxFreeAmount($shippingAmount);
        }

        return $bill;
    }
}