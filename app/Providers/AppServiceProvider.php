<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\Cart;
use App\Models\SubmissionSetting;
use App\Models\User;
use Throwable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Tampilan AdminLTE 2 pakai Bootstrap 3, jadi link pagination ikut style itu.
        Paginator::useBootstrapThree();

        Blade::directive('currency', function ($expression) {
            return "Rp. <?php echo number_format($expression,0,',','.'); ?>";
        });

        view()->composer('*', function ($view) {
            static $sharedData = [];
            // Kunci per user login / per keranjang tamu, supaya badge keranjang tidak tertukar antar pengunjung
            $cacheKey = auth()->id() ?: ('guest:' . (Cart::guestToken() ?: 'none'));

            if (!array_key_exists($cacheKey, $sharedData)) {
                $sharedData[$cacheKey] = $this->resolveSharedViewData();
            }

            $view->with($sharedData[$cacheKey]);
        });
    }

    protected function resolveSharedViewData()
    {
        $setting = null;
        $submissionOpen = false;
        $isBeforeOpen = false;
        $jumlahPeserta = 0;
        $paymentDueHours = 24;
        $landingCartCount = 0;

        try {
            if (Schema::hasTable('submission_settings')) {
                $setting = SubmissionSetting::current();
                $submissionOpen = SubmissionSetting::isOpen();
                $isBeforeOpen = $setting && now()->lessThan($setting->open_at);
            }

            if (Schema::hasTable('users')) {
                $jumlahPeserta = User::where('role', 'peserta')->count() * 2;
            }

            if (Schema::hasTable('app_settings')) {
                $paymentDueHours = AppSetting::paymentDueHours();
            }

            if (Schema::hasTable('carts') && Schema::hasTable('cart_items')) {
                // User login: keranjang akun (keranjang tamu otomatis digabung). Tamu: keranjang dari cookie.
                $landingCartCount = Cart::currentQuantity();
            }
        } catch (Throwable $exception) {
            // Allow console commands and tests to boot even when the database
            // connection is not available yet.
        }

        return [
            'setting' => $setting,
            'submissionOpen' => $submissionOpen,
            'isBeforeOpen' => $isBeforeOpen,
            'jumlahPeserta' => $jumlahPeserta,
            'paymentDueHours' => $paymentDueHours,
            'landingCartCount' => $landingCartCount,
        ];
    }
}