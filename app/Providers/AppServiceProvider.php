<?php

namespace App\Providers;

use App\Mail\Transports\ApiTransport;
use App\Models\Setting;
use App\Services\ApiMailService;
use App\Services\MailConfigService;
use App\Traits\CommonTableColumns;
use Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;
use Mail;
use Schema;

class AppServiceProvider extends ServiceProvider
{
    use CommonTableColumns;
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind('path.public', function () {
        //     return base_path('public');
        // });


        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::prohibitDestructiveCommands(app()->isProduction());
        Mail::extend('api', function (array $config) {
            return new ApiTransport(app(ApiMailService::class));
        });
        $databaseIsAvailable = false;
        try {
            DB::connection()->getPdo();
            $databaseIsAvailable = true;
        } catch (\Exception $e) {
            \Log::warning('Database is not available: ' . $e->getMessage());
        }

        if ($databaseIsAvailable && Schema::hasTable('settings')) {
            $this->app->bind(MailConfigService::class, function ($app) {
                return new MailConfigService();
            });

            if ($this->app->bound(MailConfigService::class)) {
                $mailConfig = $this->app->make(MailConfigService::class);
                Config::set('mail', $mailConfig->getMailConfiguration());
            }
        }

        $this->commonColumns();
        Builder::macro('whereLike', function ($attributes, string $searchTerm) {
            $this->where(function (Builder $query) use ($attributes, $searchTerm) {
                foreach (Arr::wrap($attributes) as $attribute) {
                    $query->when(
                        !($attribute instanceof \Illuminate\Contracts\Database\Query\Expression) &&
                        str_contains((string) $attribute, '.'),
                        function (Builder $query) use ($attribute, $searchTerm) {
                            [$relation, $relatedAttribute] = explode('.', (string) $attribute);
                            $query->orWhereHas($relation, function (Builder $query) use ($relatedAttribute, $searchTerm) {
                                $query->where($relatedAttribute, 'LIKE', "%{$searchTerm}%");
                            });
                        },
                        function (Builder $query) use ($attribute, $searchTerm) {
                            $query->orWhere($attribute, 'LIKE', "%{$searchTerm}%");
                        }
                    );
                }
            });
        });
    }
}
