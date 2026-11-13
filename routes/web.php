<?php

use App\Http\Controllers\CalculationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CuttingJobController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DyeingJobController;
use App\Http\Controllers\FabricInventoryController;
use App\Http\Controllers\FactoryExpenseController;
use App\Http\Controllers\KnittingJobController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\LotCostController;
use App\Http\Controllers\PackingJobController;
use App\Http\Controllers\PackingMaterialController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SewingJobController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\YarnController;
use App\Http\Middleware\LoadPreferences;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
Route::middleware(['auth', LoadPreferences::class])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    foreach (['body', 'cutting', 'neck', 'rib'] as $cuttingType) {
        Route::resource('cutting/'.$cuttingType, CuttingJobController::class)
            ->parameters([$cuttingType => 'cuttingJob'])->names('cutting.'.$cuttingType);
        foreach (['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'] as $action) {
            Route::getRoutes()->getByName('cutting.'.$cuttingType.'.'.$action)?->defaults('cutting_type', $cuttingType);
        }
    }
    Route::resource('staffs', StaffController::class)->parameters(['staffs' => 'staff']);
    Route::resource('yarns', YarnController::class);
    Route::post('/yarns/{yarn}/stock', [YarnController::class, 'stock'])->name('yarns.stock');
    Route::resource('companies', CompanyController::class);
    Route::resource('knitting-jobs', KnittingJobController::class);
    Route::resource('dyeing-jobs', DyeingJobController::class);
    Route::get('/fabric', [FabricInventoryController::class, 'index'])->name('fabric.index');
    Route::get('/fabric/create-market-lot', [FabricInventoryController::class, 'createMarketLot'])->name('fabric.market-lot.create');
    Route::post('/fabric/create-market-lot', [FabricInventoryController::class, 'storeMarketLot'])->name('fabric.market-lot.store');
    Route::get('/fabric/{lot}', [FabricInventoryController::class, 'show'])->name('fabric.show');
    Route::resource('sewing-jobs', SewingJobController::class);
    Route::resource('packing-jobs', PackingJobController::class);
    Route::resource('packing-materials', PackingMaterialController::class)->except(['destroy']);
    Route::post('/packing-materials/stock', [PackingMaterialController::class, 'stock'])->name('packing-materials.stock');
    Route::resource('expenses', FactoryExpenseController::class);
    Route::get('/lots/{lot}', [LotController::class, 'show'])->name('lots.show');
    Route::get('/lots/{lot}/cost-sheet', [LotCostController::class, 'show'])->name('lots.cost-sheet');
    Route::put('/lots/{lot}/cost-rates', [LotCostController::class, 'updateRates'])->name('lots.cost-rates');
    Route::get('/api/lots/preview', [CalculationController::class, 'preview'])->name('lots.preview');
    Route::get('/api/lots/{lot}/fabric', [CalculationController::class, 'fabric'])->name('lots.fabric');
    Route::post('/api/calculations', [CalculationController::class, 'calculate'])->name('calculations');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/colors/{color}', [SettingsController::class, 'color'])->name('settings.color');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
require __DIR__.'/auth.php';
