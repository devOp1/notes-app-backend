<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default admin user
        DB::table('admins')->insertOrIgnore([
            'name'       => 'Admin',
            'email'      => config('services.seeder.admin_email'),
            'password'   => Hash::make(config('services.seeder.admin_password')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Default normal user — skip in production
        if (! app()->isProduction()) {
            DB::table('users')->insertOrIgnore([
                'name'              => 'Test User',
                'email'             => config('services.seeder.default_user_email'),
                'password'          => Hash::make(config('services.seeder.default_user_password')),
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        // Passport password-grant client for the frontend
        $clientId = config('services.seeder.passport_client_id') ?? (string) Str::uuid();
        DB::table('oauth_clients')->insertOrIgnore([
            'id'            => $clientId,
            'name'          => 'Frontend Password Client',
            'secret'        => Hash::make(config('services.seeder.passport_client_secret')),
            'provider'      => 'users',
            'redirect_uris' => json_encode([]),
            'grant_types'   => json_encode(['password', 'refresh_token']),
            'revoked'       => false,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
