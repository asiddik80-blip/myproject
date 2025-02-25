<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Data\sidebar; 


use Carbon\Carbon;

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
        $this->panggilSidebar();
    }

    private function panggilSidebar(): void
        {
            // Ambil data dari method getAll() di class sidebar
            $sidebarData = sidebar::getAll();

            // Pastikan data yang diterima berupa array
            if (is_array($sidebarData)) {
                // Share data sidebar ke semua view
                View::share('sidebarMenu', $sidebarData);
            } else {
                // Jika data tidak valid, bisa memberikan error atau penanganan lain
                abort(500, 'Data sidebar tidak valid!');
            }
        }
}
