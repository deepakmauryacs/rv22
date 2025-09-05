<?php

use App\Http\Controllers\Buyer\ApiIndentController;
use Illuminate\Support\Facades\Route;

 Route::post('api-indent/add-product/{key}', [ApiIndentController::class, 'postmanStore'])->name('apiIndent.postmanStore');
