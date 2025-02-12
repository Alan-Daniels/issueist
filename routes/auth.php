<?php

use App\Http\Controllers\Auth\GithubController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/redirect', [GithubController::class, "redirect"])->name("auth/redirect");

Route::get('/auth/callback', [GithubController::class, "callback"]);
