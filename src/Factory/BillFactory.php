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
    protected Company $company;
    protected Operator $operator;

    public function __construct(string $companyOib, ?string $operatorOib, bool $companyInsideTaxRegistry)
    {
        $companyOibObject = new Oib($companyOib);
        $operatorOibObject = $companyOibObject;
        if (null !== $operatorOib && $companyOib !== $operatorOib) {
            $operatorOibObject = new Oib($operatorOib);
        }

        $this->company = new Company($companyOibObject, $companyInsideTaxRegistry);
        $this->operator = new Operator($operatorOibObject);
    }

    public function new(DateTimeImmutable $dateTime, Bill\Identifier $identifier, bool $redelivery = false)
    {
        return new Bill(
            $this->company,
            $this->operator,
            $dateTime,
            $identifier,
            Bill\PaymentType::cash(),
            Bill\SequenceType::billingDevice(),
            $redelivery
        );
    }
}