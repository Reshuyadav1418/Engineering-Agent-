<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the task seeds.
     * Idempotent: if tasks already exist, seeder is skipped entirely.
     */
    public function run(): void
    {
        // Guard: skip if tasks already seeded (safe for redeployment)
        if (Task::count() > 0) {
            $this->command->info('Tasks already exist — skipping TasksTableSeeder.');
            return;
        }

        // Ensure we have employees to assign tasks to
        $employeeIds = Employee::pluck('id')->toArray();
        if (empty($employeeIds)) {
            $this->command->warn('No employees found — skipping TasksTableSeeder.');
            return;
        }

        $statuses = ['Pending', 'In Progress', 'Completed'];

        for ($i = 0; $i < 100; $i++) {
            $status       = $statuses[array_rand($statuses)];
            $assignedDate = fake()->dateTimeBetween('-3 months', 'now');
            $completedDate = $status === 'Completed'
                ? fake()->dateTimeBetween($assignedDate, 'now')
                : null;

            Task::create([
                'employee_id'     => $employeeIds[array_rand($employeeIds)],
                'title'           => fake()->sentence(6),
                'description'     => fake()->paragraph(),
                'status'          => $status,
                'assigned_date'   => $assignedDate->format('Y-m-d'),
                'completed_date'  => $completedDate ? $completedDate->format('Y-m-d') : null,
                'estimated_hours' => fake()->randomFloat(2, 1, 40),
                'actual_hours'    => $status === 'Completed' ? fake()->randomFloat(2, 1, 40) : 0,
            ]);
        }

        $this->command->info('100 tasks seeded.');
    }
}
