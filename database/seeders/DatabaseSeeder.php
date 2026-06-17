<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Idempotent: safe to run multiple times — will not create duplicate records.
     */
    public function run(): void
    {
        // Only seed users if none exist yet (prevents duplicates on redeployment)
        if (User::count() === 0) {
            User::factory(10)->create();
            $this->command->info('Users seeded.');
        } else {
            $this->command->info('Users already exist — skipping.');
        }

        $this->call([
            \Database\Seeders\EmployeesTableSeeder::class,
            \Database\Seeders\TasksTableSeeder::class,
        ]);
    }
}
