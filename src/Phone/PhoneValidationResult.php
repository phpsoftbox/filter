<?php

declare(strict_types=1);

namespace PhpSoftBox\Filter\Phone;

use LogicException;

final class PhoneValidationResult
{
    public const int ERROR_PHONE_EMPTY             = 1;
    public const int ERROR_INCORRECT_OPERATOR_CODE = 2;
    public const int ERROR_INVALID_LENGTH          = 3;

    private bool $isValid   = false;
    private ?string $value  = null;
    private ?int $errorCode = null;

    public const array ERROR_MESSAGES = [
        self::ERROR_PHONE_EMPTY             => 'Номер телефона не указан.',
        self::ERROR_INCORRECT_OPERATOR_CODE => 'Указан некорректный код оператора.',
        self::ERROR_INVALID_LENGTH          => 'Номер телефона имеет некорректную длину.',
    ];

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        if ($this->errorCode === null) {
            return null;
        }

        return self::ERROR_MESSAGES[$this->errorCode] ?? null;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getValue(): string
    {
        if ($this->value === null) {
            throw new LogicException('Value is not set.');
        }

        return $this->value;
    }

    public static function makeSuccess(string $normalized): self
    {
        $result = new self();

        $result->isValid = true;
        $result->value   = $normalized;

        return $result;
    }

    public static function makeError(int $errorCode): self
    {
        $result = new self();

        $result->isValid   = false;
        $result->errorCode = $errorCode;

        return $result;
    }
}
