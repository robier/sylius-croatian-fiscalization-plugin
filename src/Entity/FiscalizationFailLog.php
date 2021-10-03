<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Entity;

use DateTimeImmutable;
use Robier\Fiscalization\Bill;
use Sylius\Component\Core\Model\OrderInterface;

class FiscalizationFailLog
{
    private int $id;

    public function __construct(
        private array $reasons,
        private DateTimeImmutable $created,
        private OrderInterface $order
    ) {
        // noop
    }

    public function getId(): int
    {
        return $this->id();
    }

    public function id(): int
    {
        return $this->id;
    }

    public function reasons(): array
    {
        return $this->reasons;
    }

    public function created(): DateTimeImmutable
    {
        return $this->created;
    }
    public function order(): OrderInterface
    {
        return $this->order;
    }
}
