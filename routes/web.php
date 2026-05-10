<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FinanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\AuthController;
use App\Http\Middleware\SpaiAuthMiddleware;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/pin', [AuthController::class, 'showPinForm'])->name('login.pin');
Route::post('/pin', [AuthController::class, 'verifyPin'])->name('login.pin.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware([SpaiAuthMiddleware::class])->group(function () {
    Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/dashboard', [FinanceController::class, 'index'])->name('dashboard');
    
    // Donations
    Route::get('/donations', [FinanceController::class, 'indexDonations'])->name('donations.index');
    Route::get('/donations/create', [FinanceController::class, 'createDonation'])->name('donations.create');
    Route::post('/donations', [FinanceController::class, 'storeDonation'])->name('donations.store');

    // Disbursements
    Route::get('/disbursements', [FinanceController::class, 'indexDisbursements'])->name('disbursements.index');
    Route::get('/disbursements/create', [FinanceController::class, 'createDisbursement'])->name('disbursements.create');
    Route::post('/disbursements', [FinanceController::class, 'storeDisbursement'])->name('disbursements.store');

    // Projects
    Route::get('/projects', [FinanceController::class, 'indexProjects'])->name('projects.index');
    Route::get('/projects/create', [FinanceController::class, 'createProject'])->name('projects.create');
    Route::post('/projects', [FinanceController::class, 'storeProject'])->name('projects.store');
    Route::get('/projects/{project}', [FinanceController::class, 'showProject'])->name('projects.show');

    // Programs
    Route::resource('programs', App\Http\Controllers\ProgramController::class, ['names' => 'programs']);

    // Reports
    Route::get('/report/receipts', [FinanceController::class, 'reportReceipts'])->name('report.receipts');
    Route::get('/report/disbursements', [FinanceController::class, 'reportDisbursements'])->name('report.disbursements');
    Route::get('/report/amil', [FinanceController::class, 'reportAmil'])->name('report.amil');

    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/global', [App\Http\Controllers\SettingController::class, 'updateGlobal'])->name('settings.update_global');
    Route::post('/settings/program/{program}', [App\Http\Controllers\SettingController::class, 'updateProgram'])->name('settings.update_program');
    Route::post('/settings/project/{project}', [App\Http\Controllers\SettingController::class, 'updateProject'])->name('settings.update_project');
    Route::post('/settings/pillars', [App\Http\Controllers\SettingController::class, 'updatePillars'])->name('settings.update_pillars');

    // Utilities
    Route::post('/bulk-delete', [FinanceController::class, 'bulkDelete'])->name('bulk_delete');
    Route::get('/export-excel', [FinanceController::class, 'exportExcel'])->name('export_excel');
    });
});
