<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Company\Presentation\Http\Controllers\Api\CompanyController;

Route::post('company', [CompanyController::class, 'upsert'])->name('company.upsert');
Route::get('company/{edrpou}/versions', [CompanyController::class, 'versions'])->name('company.versions');
