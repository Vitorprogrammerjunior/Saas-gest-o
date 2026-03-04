<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| API Documentation (Swagger UI)
|--------------------------------------------------------------------------
|
| Protected routes — only accessible when APP_ENV=local or
| API_DOCS_ENABLED=true in .env
|
| Access: http://localhost:8000/docs
|
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
