<?php

namespace App\DTOs\Setting;

readonly class SettingUpdateDTO
{
    public function __construct(
        public string $key,
        public mixed $value,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'],
            value: $data['value'],
        );
    }

    public static function fromRequest(string $key, mixed $value): self
    {
        return new self(
            key: $key,
            value: $value,
        );
    }
}