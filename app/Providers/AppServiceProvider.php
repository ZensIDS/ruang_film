<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\CartItem;
use App\Models\SubmissionSetting;
use App\Models\User;
use Throwable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;

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
        Blade::directive('currency', function ($expression) {
            return "Rp. <?php echo number_format($expression,0,',','.'); ?>";
        });

        view()->composer('*', function ($view) {
            static $sharedData = [];
            $cacheKey = auth()->id() ?: 'guest';

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

            if (auth()->id() && Schema::hasTable('carts') && Schema::hasTable('cart_items')) {
                $landingCartCount = (int) CartItem::query()
                    ->whereHas('cart', function ($query) {
                        $query->where('user_id', auth()->id());
                    })
                    ->sum('quantity');
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
