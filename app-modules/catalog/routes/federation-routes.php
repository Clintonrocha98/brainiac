<?php

declare(strict_types=1);

use He4rt\Catalog\Federation\Http\ReceiveSnapshotController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/snapshot', ReceiveSnapshotController::class);
