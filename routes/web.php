<?php

use Illuminate\Support\Facades\Route;

Route::get('/app/{any?}', function () {
    return redirect('/'.ltrim(request()->path() === 'app' ? '' : request()->route('any', ''), '/'));
})->where('any', '.*');

// Vite's compiled files live under /build. It is an asset namespace, not an
// SPA namespace; invalid URLs below it must not render the application shell.
Route::get('/build/{any?}', fn () => abort(404))->where('any', '.*');

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api(?:/|$)|build(?:/|$)|storage(?:/|$)).*$');
