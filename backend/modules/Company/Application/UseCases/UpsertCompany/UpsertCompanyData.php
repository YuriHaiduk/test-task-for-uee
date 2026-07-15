<?php

declare(strict_types=1);

namespace Modules\Company\Application\UseCases\UpsertCompany;

/**
 * Typed input for the upsert-company use case.
 *
 * Built by the Presentation layer (Form Request) so the Application layer never
 * receives a loosely-typed request array.
 */
final readonly class UpsertCompanyData
{
    public function __construct(
        public string $name,
        public string $edrpou,
        public string $address,
    ) {}

    /**
     * The attributes to persist on the company model.
     *
     * @return array<string, string>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'edrpou' => $this->edrpou,
            'address' => $this->address,
        ];
    }
}
