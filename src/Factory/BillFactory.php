<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Factory;

use DateTimeImmutable;
use Robier\Fiscalization\Bill;
use Robier\Fiscalization\Company;
use Robier\Fiscalization\Oib;
use Robier\Fiscalization\Operator;

final class BillFactory
{
//    public function __construct(string $companyOib, ?string $operatorOib, bool $companyInsideTaxRegistry, string $paymentType, string $)
//    {
//
//    }

    public function new(DateTimeImmutable $dateTime)
    {
        $oib = new Oib('13074646146');
        $company = new Company($oib, true);
        $operator = new Operator($oib);

        return new Bill(
            $company,
            $operator,
            $dateTime,

        );
    }
}