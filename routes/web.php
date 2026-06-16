<?php

use App\Http\Controllers\Annulation\AnnulationController;
use App\Http\Controllers\Admin\LoginHistoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\UserGroupController;
use App\Http\Controllers\Caisse\CaisseSessionController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Facturation\FactureController;
use App\Http\Controllers\Fournisseur\FournisseurController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Security\SecurityController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Caisse\VenteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Journal\JournalController;
use App\Http\Controllers\Rapport\RapportController;
use App\Http\Controllers\Retour\RetourController;
use App\Http\Controllers\Setting\SettingController;
use App\Http\Controllers\Stock\InventorySessionController;
use App\Http\Controllers\Stock\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:users.manage')->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:roles.manage')->prefix('admin/roles')->name('admin.roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    });

    Route::middleware('permission:products.manage')->prefix('admin/products')->name('admin.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:categories.manage')->prefix('admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:vente.create')->prefix('caisse')->name('caisse.')->group(function () {
        Route::get('/', [VenteController::class, 'index'])->name('index');
        Route::post('/vente', [VenteController::class, 'store'])->name('store');
    });

    Route::middleware('permission:vente.view')->group(function () {
        Route::get('/caisse/historique', [VenteController::class, 'historique'])->name('caisse.historique');
        Route::get('/caisse/vente/{vente}', [VenteController::class, 'show'])->name('caisse.show');
    });

    Route::middleware('permission:vente.print')->group(function () {
        Route::get('/caisse/ticket/{vente}', [VenteController::class, 'ticket'])->name('caisse.ticket');
    });

    Route::middleware('permission:stock.view')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/mouvements', [StockController::class, 'mouvements'])->name('mouvements');
        Route::get('/analyse', [StockController::class, 'analyse'])->name('analyse');
        Route::get('/inventaires', [InventorySessionController::class, 'index'])->name('inventories.index');
        Route::get('/inventaires/nouveau', [InventorySessionController::class, 'create'])->name('inventories.create');
        Route::get('/inventaires/{inventory}', [InventorySessionController::class, 'show'])->name('inventories.show');
        Route::get('/inventaires/{inventory}/rapport', [InventorySessionController::class, 'report'])->name('inventories.report');
    });

    Route::middleware('permission:stock.inventory')->group(function () {
        Route::post('/stock/inventaires', [InventorySessionController::class, 'store'])->name('stock.inventories.store');
        Route::put('/stock/inventaires/{inventory}', [InventorySessionController::class, 'update'])->name('stock.inventories.update');
        Route::post('/stock/inventaires/{inventory}/annuler', [InventorySessionController::class, 'cancel'])->name('stock.inventories.cancel');
    });

    Route::middleware('permission:stock.entry')->post('/stock/entree', [StockController::class, 'entree'])->name('stock.entree');
    Route::middleware('permission:stock.exit')->post('/stock/sortie', [StockController::class, 'sortie'])->name('stock.sortie');
    Route::middleware('permission:stock.inventory')->post('/stock/inventaire', [StockController::class, 'inventaire'])->name('stock.inventaire');
    Route::middleware('permission:stock.import')->post('/stock/import', [StockController::class, 'import'])->name('stock.import');

    Route::middleware('permission:retour.manage')->prefix('retours')->name('retours.')->group(function () {
        Route::get('/', [RetourController::class, 'index'])->name('index');
        Route::get('/create', [RetourController::class, 'create'])->name('create');
        Route::post('/', [RetourController::class, 'store'])->name('store');
        Route::get('/{retour}', [RetourController::class, 'show'])->name('show');
    });

    Route::middleware('permission:annulation.manage')->prefix('annulations')->name('annulations.')->group(function () {
        Route::get('/', [AnnulationController::class, 'index'])->name('index');
        Route::post('/', [AnnulationController::class, 'store'])->name('store');
    });

    Route::middleware('permission:rapports.view')->get('/rapports', [RapportController::class, 'index'])->name('rapports.index');

    Route::middleware('permission:journal.view')->get('/journal', [JournalController::class, 'index'])->name('journal.index');

    Route::middleware('permission:groups.manage')->prefix('admin/groups')->name('admin.groups.')->group(function () {
        Route::get('/', [UserGroupController::class, 'index'])->name('index');
        Route::post('/', [UserGroupController::class, 'store'])->name('store');
        Route::put('/{group}', [UserGroupController::class, 'update'])->name('update');
        Route::delete('/{group}', [UserGroupController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:login_history.view')->get('/admin/connexions', [LoginHistoryController::class, 'index'])->name('admin.login-history.index');
    Route::middleware('permission:permissions.manage')->get('/admin/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');

    Route::middleware('permission:caisse.session')->prefix('caisse/sessions')->name('caisse.sessions.')->group(function () {
        Route::get('/', [CaisseSessionController::class, 'index'])->name('index');
        Route::post('/ouvrir', [CaisseSessionController::class, 'ouvrir'])->name('ouvrir');
        Route::post('/fermer', [CaisseSessionController::class, 'fermer'])->name('fermer');
    });

    Route::middleware('permission:facturation.view')->prefix('facturation')->name('facturation.')->group(function () {
        Route::get('/', [FactureController::class, 'index'])->name('index');
        Route::get('/proforma/nouvelle', [FactureController::class, 'create'])->name('create');
        Route::post('/proforma', [FactureController::class, 'store'])->name('store');
        Route::get('/{facture}/imprimer', [FactureController::class, 'imprimer'])->name('imprimer');
        Route::get('/{facture}', [FactureController::class, 'show'])->name('show');
        Route::post('/{facture}/reimprimer', [FactureController::class, 'reimprimer'])->name('reimprimer');
    });

    Route::middleware('permission:clients.manage')->prefix('clients')->name('clients.')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->name('index');
        Route::post('/', [ClientController::class, 'store'])->name('store');
    });
    Route::middleware('permission:credits.manage')->get('/clients/credits', [ClientController::class, 'credits'])->name('clients.credits');

    Route::middleware('permission:fournisseurs.manage')->prefix('fournisseurs')->name('fournisseurs.')->group(function () {
        Route::get('/', [FournisseurController::class, 'index'])->name('index');
        Route::post('/', [FournisseurController::class, 'store'])->name('store');
        Route::get('/reglements', [FournisseurController::class, 'reglements'])->name('reglements');
        Route::post('/reglements', [FournisseurController::class, 'storeReglement'])->name('reglements.store');
    });

    Route::middleware('permission:notifications.view')->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });

    Route::middleware('permission:security.manage')->get('/securite', [SecurityController::class, 'index'])->name('security.index');

    Route::middleware('permission:settings.manage')->prefix('parametres')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::put('/', [SettingController::class, 'update'])->name('update');
    });
});
