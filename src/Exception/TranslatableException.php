<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\Exception;

use Exception;

final class TranslatableException extends Exception
{
    public function __construct(
        string $message,
        private string $translatableKey
    )
    {
        parent::__construct($message);
    }

    public function translatableKey(): string
    {
        return $this->translatableKey;
    }
}