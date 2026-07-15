<?php

declare(strict_types=1);

namespace Modules\Company\Presentation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Versioning\Application\VersionSnapshot;

/**
 * @mixin VersionSnapshot
 */
class VersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'version' => $this->version,
            'snapshot' => $this->data,
            'created_at' => $this->createdAt,
        ];
    }
}
