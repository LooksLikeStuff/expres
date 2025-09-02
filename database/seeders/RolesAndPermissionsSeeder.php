<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'add_partner',
            'add_coordinator',
            'edit_executor_profile',
            'delete_coordinator',
            'restrict_access_to_executor',
            'create_deal',
            'delete_deal',
            'edit_order',
            'create_second_deal_chat_without_client_or_partner',
            'upload_files_to_deal',
            'create_unrelated_chat',
            'can_send_first_pm',
            'add_people_to_chat',
            'fill_brief',
            'set_project_ratings',
            'edit_deal_progress_and_final',
            'pin_messages_in_chat',
            'delete_messages_in_chat',
            'edit_brief',
            'edit_brief_completion',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Client
        $client = Role::firstOrCreate(['name' => 'client']);
        $client->givePermissionTo([
            'fill_brief',
            'set_project_ratings',
            'pin_messages_in_chat',
        ]);

        // Partner
        $partner = Role::firstOrCreate(['name' => 'partner']);
        $partner->givePermissionTo([
            'create_deal',
            'upload_files_to_deal',
            'set_project_ratings',
            'pin_messages_in_chat',
        ]);

        // Coordinator
        $coordinator = Role::firstOrCreate(['name' => 'coordinator']);
        $coordinator->givePermissionTo([
            'create_second_deal_chat_without_client_or_partner',
            'upload_files_to_deal',
            'can_send_first_pm',
            'add_people_to_chat',
            'set_project_ratings',
            'edit_deal_progress_and_final',
            'pin_messages_in_chat',
            'delete_messages_in_chat',
        ]);

        // Admin/Developer
        $adminDeveloper = Role::firstOrCreate(['name' => 'admin']);
        $adminDeveloper->givePermissionTo([
            'add_partner',
            'add_coordinator',
            'edit_executor_profile',
            'delete_coordinator',
            'restrict_access_to_executor',
            'create_deal',
            'delete_deal',
            'edit_order',
            'create_second_deal_chat_without_client_or_partner',
            'upload_files_to_deal',
            'can_send_first_pm',
            'add_people_to_chat',
            'fill_brief',
            'set_project_ratings',
            'edit_deal_progress_and_final',
            'pin_messages_in_chat',
            'delete_messages_in_chat',
        ]);

        // Executor
        $executor = Role::firstOrCreate(['name' => 'executor']);
        $executor->givePermissionTo([
            'pin_messages_in_chat',
        ]);

        // Department Head
        $departmentHead = Role::firstOrCreate(['name' => 'department_head']);
        $departmentHead->givePermissionTo([
            'add_partner',
            'add_coordinator',
            'edit_executor_profile',
            'delete_coordinator',
            'restrict_access_to_executor',
            'create_deal',
            'delete_deal',
            'edit_order',
            'create_second_deal_chat_without_client_or_partner',
            'upload_files_to_deal',
            'create_unrelated_chat',
            'can_send_first_pm',
            'add_people_to_chat',
            'fill_brief',
            'set_project_ratings',
            'edit_deal_progress_and_final',
            'pin_messages_in_chat',
            'delete_messages_in_chat',
        ]);

        // Company Head
        $companyHead = Role::firstOrCreate(['name' => 'company_head']);
        $companyHead->givePermissionTo([
            'add_partner',
            'add_coordinator',
            'edit_executor_profile',
            'delete_coordinator',
            'restrict_access_to_executor',
            'create_deal',
            'delete_deal',
            'edit_order',
            'create_second_deal_chat_without_client_or_partner',
            'upload_files_to_deal',
            'create_unrelated_chat',
            'can_send_first_pm',
            'add_people_to_chat',
            'fill_brief',
            'set_project_ratings',
            'edit_deal_progress_and_final',
            'pin_messages_in_chat',
            'delete_messages_in_chat',
        ]);

        // Senior Coordinator
        $seniorCoordinator = Role::firstOrCreate(['name' => 'senior_coordinator']);
        $seniorCoordinator->givePermissionTo([
            'edit_executor_profile',
            'restrict_access_to_executor',
            'create_deal',
            'create_second_deal_chat_without_client_or_partner',
            'create_unrelated_chat',
            'can_send_first_pm',
            'add_people_to_chat',
            'set_project_ratings',
            'edit_deal_progress_and_final',
            'pin_messages_in_chat',
            'delete_messages_in_chat',
        ]);

        // Curator
        $curator = Role::firstOrCreate(['name' => 'curator']);
        $curator->givePermissionTo([
            'can_send_first_pm',
        ]);

        // OTM Manager
        $otmManager = Role::firstOrCreate(['name' => 'otm_manager']);
        $otmManager->givePermissionTo([
            'create_deal',
            'upload_files_to_deal',
            'can_send_first_pm',
        ]);
    }
}
