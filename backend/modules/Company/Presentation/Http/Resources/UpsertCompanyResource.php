<?php

declare(strict_types=1);

namespace Modules\Company\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Company\Application\UseCases\UpsertCompany\UpsertCompanyResultDto;

/**
 * @mixin UpsertCompanyResultDto
 */
final class UpsertCompanyResource extends JsonResource
{
    /**
     * Disable the top-level "data" wrapper so the task's flat
     * { status, company_id, version } shape is returned as-is.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status->value,
            'company_id' => $this->companyId,
            'version' => $this->version,
        ];
    }
}
