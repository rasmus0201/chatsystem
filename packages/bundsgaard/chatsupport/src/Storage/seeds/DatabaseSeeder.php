<?php

namespace Bundsgaard\ChatSupport\Storage\Seeds;

use Illuminate\Database\Seeder;
use Bundsgaard\ChatSupport\Storage\UserStatus;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        UserStatus::create([
            'priority' => 0,
            'name' => 'Banned',
            'slug' => 'banned',
        ]);

        UserStatus::create([
            'priority' => 4,
            'name' => 'Disconnected',
            'slug' => 'disconnected',
        ]);

        UserStatus::create([
            'priority' => 8,
            'name' => 'Inactive',
            'slug' => 'inactive',
        ]);

        UserStatus::create([
            'priority' => 12,
            'name' => 'Active',
            'slug' => 'active',
        ]);
    }
}
