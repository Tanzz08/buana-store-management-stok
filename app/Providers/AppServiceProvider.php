<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\LowStockNotification;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockAdjustment;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Policies\CategoryPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReportPolicy;
use App\Policies\SalePolicy;
use App\Policies\StockAdjustmentPolicy;
use App\Policies\StockInPolicy;
use App\Policies\SupplierPolicy;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Repositories\Contracts\StockInRepositoryInterface;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentCategoryRepository;
use App\Repositories\Eloquent\EloquentNotificationRepository;
use App\Repositories\Eloquent\EloquentProductRepository;
use App\Repositories\Eloquent\EloquentSaleRepository;
use App\Repositories\Eloquent\EloquentStockAdjustmentRepository;
use App\Repositories\Eloquent\EloquentStockInRepository;
use App\Repositories\Eloquent\EloquentSupplierRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, EloquentSupplierRepository::class);
        $this->app->bind(StockInRepositoryInterface::class, EloquentStockInRepository::class);
        $this->app->bind(SaleRepositoryInterface::class, EloquentSaleRepository::class);
        $this->app->bind(StockAdjustmentRepositoryInterface::class, EloquentStockAdjustmentRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, EloquentNotificationRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Daftarkan semua Laravel Policies
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(StockIn::class, StockInPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(StockAdjustment::class, StockAdjustmentPolicy::class);
        Gate::policy(LowStockNotification::class, NotificationPolicy::class);

        // ReportPolicy tidak punya model Eloquent langsung — daftarkan via Gate::define
        // Diakses dengan: Gate::allows('viewInventory') / $this->authorize('viewInventory')
        Gate::define('report.viewInventory', [ReportPolicy::class, 'viewInventory']);
        Gate::define('report.viewSales', [ReportPolicy::class, 'viewSales']);
        Gate::define('report.viewStockMutation', [ReportPolicy::class, 'viewStockMutation']);
        Gate::define('report.export', [ReportPolicy::class, 'export']);
    }
}

