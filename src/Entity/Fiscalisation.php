<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Entity;

use DateTimeImmutable;
use Sylius\Component\Core\Model\OrderInterface;

final class Fiscalisation
{
    public function __construct(
        private string $id,
        private string $securityCode,
        private int $total,
        private string $sequenceNumber,
        private DateTimeImmutable $created,
        private OrderInterface $order,
    ) {
        // noop
    }

    public function getId(): string
    {
        return $this->id();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function securityCode(): string
    {
        return $this->securityCode;
    }

    public function created(): DateTimeImmutable
    {
        return $this->created;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function sequenceNumber(): string
    {
        return $this->sequenceNumber;
    }

    public function order(): OrderInterface
    {
        return $this->order;
    }
}