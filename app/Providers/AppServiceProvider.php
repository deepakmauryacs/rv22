<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(255);
        Paginator::useBootstrapFive();

        Builder::macro('whereLike', function ($columns, $search) {
            return $this->where(function ($query) use ($columns, $search) {
                foreach ((array) $columns as $column) {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        });
    }
}
