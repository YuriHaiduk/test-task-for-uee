<?php

declare(strict_types=1);

namespace Modules\Company\Presentation\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Company\Application\UseCases\ListCompanyVersions\ListCompanyVersionsService;
use Modules\Company\Application\UseCases\UpsertCompany\UpsertCompanyService;
use Modules\Company\Presentation\Http\Requests\StoreCompanyRequest;
use Modules\Company\Presentation\Http\Resources\UpsertCompanyResource;
use Modules\Company\Presentation\Http\Resources\VersionResource;
use Modules\Versioning\Application\Enums\VersionStatus;
use Symfony\Component\HttpFoundation\Response;

final class CompanyController extends Controller
{
    /**
     * Create or update a company, recording a version on change.
     */
    public function upsert(StoreCompanyRequest $request, UpsertCompanyService $upsertCompany): JsonResponse
    {
        $result = $upsertCompany->handle($request->toData());

        $httpStatus = $result->status === VersionStatus::Created
            ? Response::HTTP_CREATED
            : Response::HTTP_OK;

        return UpsertCompanyResource::make($result)
            ->response()
            ->setStatusCode($httpStatus);
    }

    /**
     * List every recorded version for a company, newest first.
     */
    public function versions(string $edrpou, ListCompanyVersionsService $listVersions): JsonResponse
    {
        return VersionResource::collection($listVersions->handle($edrpou))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
