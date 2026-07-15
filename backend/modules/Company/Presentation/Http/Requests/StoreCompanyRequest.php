<?php

declare(strict_types=1);

namespace Modules\Company\Presentation\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Company\Application\UseCases\UpsertCompany\UpsertCompanyData;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize the input before validation.
     *
     * Trimming here keeps the module self-contained instead of relying on the
     * application's global TrimStrings middleware, and prevents whitespace-only
     * differences (e.g. a leading space in the address) from being treated as a
     * real change and creating a redundant version.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(
            collect(['name', 'edrpou', 'address'])
                ->filter(fn (string $field): bool => is_string($this->input($field)))
                ->mapWithKeys(fn (string $field): array => [$field => trim($this->input($field))])
                ->all(),
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:256'],
            'edrpou' => ['required', 'string', 'max:10'],
            'address' => ['required', 'string'],
        ];
    }

    /**
     * Map the validated input into the use-case input DTO.
     */
    public function toData(): UpsertCompanyData
    {
        return new UpsertCompanyData(
            name: (string) $this->validated('name'),
            edrpou: (string) $this->validated('edrpou'),
            address: (string) $this->validated('address'),
        );
    }
}
