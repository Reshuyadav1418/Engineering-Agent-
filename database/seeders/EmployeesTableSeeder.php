<?php

namespace Database\Seeders;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the employee seeds.
     * Idempotent: if employees already exist, seeder is skipped entirely.
     */
    public function run(): void
    {
        // Guard: skip if employees already seeded (safe for redeployment)
        if (Employee::count() > 0) {
            $this->command->info('Employees already exist — skipping EmployeesTableSeeder.');
            return;
        }

        $faker = FakerFactory::create();
        for ($i = 0; $i < 10; $i++) {
            $employee = Employee::create([
                'name'            => $faker->name,
                'email'           => $faker->unique()->safeEmail,
                'department'      => $faker->randomElement(['Engineering', 'Marketing', 'HR', 'Sales', 'Support']),
                'role'            => $faker->jobTitle,
                'github_username' => $faker->userName,
            ]);

            // Seed attendance and working hours for the last 14 days
            for ($day = 14; $day >= 0; $day--) {
                $date = now()->subDays($day);

                // Skip weekends by default
                if ($date->isWeekend()) {
                    continue;
                }

                $status = $faker->randomElement(['Present', 'Present', 'Present', 'Present', 'Present', 'Late', 'Late', 'Absent', 'Leave']);

                Attendance::create([
                    'employee_id'     => $employee->id,
                    'attendance_date' => $date->toDateString(),
                    'status'          => $status,
                ]);

                if ($status === 'Present') {
                    WorkingHour::create([
                        'employee_id'  => $employee->id,
                        'work_date'    => $date->toDateString(),
                        'hours_worked' => $faker->randomFloat(2, 7.5, 9.5),
                    ]);
                } elseif ($status === 'Late') {
                    WorkingHour::create([
                        'employee_id'  => $employee->id,
                        'work_date'    => $date->toDateString(),
                        'hours_worked' => $faker->randomFloat(2, 5.0, 7.5),
                    ]);
                }
            }
        }

        $this->command->info('10 employees seeded with attendance and working hours.');
    }
}
