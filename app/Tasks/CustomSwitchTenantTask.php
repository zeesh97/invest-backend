<?php

namespace App\Tasks;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Concerns\UsesMultitenancyConfig;
use Spatie\Multitenancy\Exceptions\InvalidConfiguration;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Illuminate\Support\Facades\Config;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomSwitchTenantTask implements SwitchTenantTask
{
    use UsesTenantConnection;

    public function makeCurrent(Tenant $tenant): void
    {
        $tenantConnection = $tenant->getDatabaseConnectionName();
        if($tenant->database == "customer1"){
            config()->set('database.connections.'.$this->tenantDatabaseConnectionName(), config('database.connections.'.$tenantConnection));

        }

    }

    public function forgetCurrent(): void
    {
        config()->set('database.connections.'.$this->tenantDatabaseConnectionName(), null);
    }

    // protected function setTenantConnectionDatabaseName(?string $databaseName)
    // {
    //     $tenantConnectionName = $this->tenantDatabaseConnectionName();

    //     if ($tenantConnectionName === $this->landlordDatabaseConnectionName()) {
    //         throw InvalidConfiguration::tenantConnectionIsEmptyOrEqualsToLandlordConnection();
    //     }

    //     if (is_null(config("database.connections.{$tenantConnectionName}"))) {
    //         throw InvalidConfiguration::tenantConnectionDoesNotExist($tenantConnectionName);
    //     }

    //     config([
    //         "database.connections.{$tenantConnectionName}.database" => $databaseName,
    //     ]);

    //     app('db')->extend($tenantConnectionName, function ($config, $name) use ($databaseName) {
    //         $config['database'] = $databaseName;

    //         return app('db.factory')->make($config, $name);
    //     });

    //     DB::purge($tenantConnectionName);

    //     // Octane will have an old `db` instance in the Model::$resolver.
    //     Model::setConnectionResolver(app('db'));
    // }
}
