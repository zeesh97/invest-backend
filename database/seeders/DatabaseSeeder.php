<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Multitenancy\Models\Tenant;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tenant::checkCurrent()
        // ? $this->runTenantSpecificSeeders()
        // : $this->runLandlordSpecificSeeders();
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(SetupFieldSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(TaskStatusNameSeeder::class);
        $this->call(ApproversTableSeeder::class);
        $this->call(ApproverUserTableSeeder::class);
        $this->call(SubscribersTableSeeder::class);
        $this->call(SubscriberUserTableSeeder::class);
        $this->call(MasterDataManagementFormSeeder::class);
        $this->call(AddFormMasterDataManagementForm::class);
        $this->call(QaFeedbackViewAllSeeder::class);
        $this->call(CRFConditionsAddedSeeder::class);
        $this->call(MdmCategorySeeder::class);
        $this->call(CategoriesAddedSeeder::class);
        $this->call(SCRFConditionsAddedSeeder::class);
        $this->call(AutoApproveConditionAddedSeeder::class);
        $this->call(DeploymentFormUpdateSeeder::class);
        $this->call(AddFormSapAccessForm::class);
        $this->call(SapAccessFormSeeder::class);
        $this->call(AddSlugToFormsSeeder::class);
        $this->call(RequestSupportFormSeeder::class);
        $this->call(ServiceFormSeeder::class);
        $this->call(RequestSupportDeskSeeder::class);
        $this->call(NonFormSeeder::class);
        $this->call(CallbackSeeder::class);
        $this->call(OtherDependentSeeder::class);
        $this->call(CRFSettingPermissionSeeder::class);
        $this->call(MDMProjectSeeder::class);
        $this->call(ProjectSeeder::class);
        $this->call(AutoAssignTaskPermissionSeeder::class);
    }

    // public function runTenantSpecificSeeders()
    // {
    //     $this->call(UserSeeder::class);
    // }

    // public function runLandlordSpecificSeeders()
    // {
    //     // run landlord specific seeders
    // }
}
