<?php

use App\Providers\AppServiceProvider;
use Modules\Company\CompanyServiceProvider;
use Modules\Versioning\VersioningServiceProvider;

return [
    AppServiceProvider::class,
    VersioningServiceProvider::class,
    CompanyServiceProvider::class,
];
