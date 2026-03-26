<?php

use App\Http\Controllers\AdministrationController;
use App\Http\Controllers\CnpsRequestController;
use App\Http\Controllers\CustomsRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Models\City;
use App\Models\Commune;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'login');

Route::middleware(['auth', 'verified'])->prefix('dashboard')->group(function () {
  Route::get('/', [DashboardController::class,'index'])->name('dashboard');
  Route::get('administration-list', [AdministrationController::class, 'index'])->name('administration-services-list');
  Route::get('administration/{administration}', [AdministrationController::class, 'show'])->name('administration-show');
  Route::get('/service/{serviceKey}/form/{userType}', [AdministrationController::class, 'showServiceForm']) ->name('service.form');


  Route::get('/documents', [DocumentController::class, 'index'])->name('document.index');
  Route::get('/documents/{type}/{id}', [DocumentController::class, 'show'])->name('document.show');
  Route::get('/documents/{type}/{id}/download', [DocumentController::class, 'download'])->name('document.download');
});

Route::middleware(['auth'])->prefix('cnps')->name('cnps.')->group(function () {
    // 1. Affichage du formulaire
    Route::get('/declaration', [CnpsRequestController::class, 'create'])->name('create');
    // 2. Traitement des données (POST)
    Route::post('/submit', [CnpsRequestController::class, 'submit'])->name('submit');
    // 3. Page de succès finale (GET)
    Route::get('/success/{reference}', [CnpsRequestController::class, 'success'])->name('success');

});

Route::middleware(['auth'])->prefix('guichet')->name('guichet.')->group(function () {
  // 1. Affichage du formulaire
  Route::prefix('douane')->group(function(){
        Route::get('/declaration', [CustomsRequestController::class, 'create'])->name('create');
        // 2. Traitement des données (POST)
        Route::post('/submit', [CustomsRequestController::class, 'store'])->name('douane.store');
        // 3. Page de succès finale (GET)
        Route::get('/success/{reference}', [CustomsRequestController::class, 'success'])->name('douane.success');
  });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('countries/{countryId}/cities', function ($cityId) {
  $cities =  City::where('country_id', $cityId)->orderBy('name', 'ASC')->get();
  return response()->json([
    'success' =>true,
    'data'    => $cities
  ], 200);
});

Route::get('cities/{cityId}/communes', function ($cityId) {
  $communes =  Commune::where('city_id', $cityId)->orderBy('name', 'ASC')->get();
  return response()->json([
    'success' =>true,
    'data'    => $communes
  ], 200);
});

require __DIR__.'/auth.php';
