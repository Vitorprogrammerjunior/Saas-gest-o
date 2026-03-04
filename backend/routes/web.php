<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Documentation (Swagger UI)
|--------------------------------------------------------------------------
*/
Route::middleware('api.docs')->group(function () {
    Route::get('/docs', function () {
        return response()->file(resource_path('docs/swagger.html'));
    });

    Route::get('/docs/openapi.yaml', function () {
        return response()->file(
            resource_path('docs/openapi.yaml'),
            ['Content-Type' => 'application/x-yaml']
        );
    });
});

/*
|--------------------------------------------------------------------------
| SPA Catch-All
|--------------------------------------------------------------------------
| In production, the Vue SPA is built into public/.
| This catch-all serves index.html for any non-API route,
| allowing Vue Router to handle client-side routing.
|
*/
Route::get('/{any}', function () {
    $indexPath = public_path('index.html');

    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }

    // Fallback for local dev (no built SPA)
    return view('welcome');
})->where('any', '^(?!api|docs|sanctum|broadcasting).*$');
