<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Entity;

use DateTimeImmutable;
use Robier\Fiscalization\Bill;
use Sylius\Component\Core\Model\OrderInterface;

final class FiscalizationFailLog
{
    private int $id;
    private string $billIdentifier;

    public function __construct(
        private array $reasons,
        private DateTimeImmutable $created,
        private OrderInterface $order,
        Bill\Identifier $billIdentifier
    ) {
        $this->billIdentifier = (string) $billIdentifier;
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

    public function billIdentifier(): Bill\Identifier
    {
        return Bill\Identifier::fromString($this->billIdentifier);
    }
}