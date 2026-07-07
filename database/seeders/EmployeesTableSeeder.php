<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Total employees to seed.
     */
    private const TARGET_COUNT = 10000;

    /**
     * Records per Employee::insert() batch.
     */
    private const EMP_CHUNK = 1000;

    /**
     * Employees processed per attendance/working-hours batch
     * (keeps memory bounded when building related rows).
     */
    private const ATT_BATCH = 500;

    /**
     * Run the employee seeds.
     *
     * Idempotent: if 10,000 or more employees already exist the seeder is
     * skipped entirely, so running `php artisan db:seed --force` multiple
     * times is safe.
     */
    public function run(): void
    {
        // ── Idempotency guard ─────────────────────────────────────────────
        if (Employee::count() >= self::TARGET_COUNT) {
            $this->command->info(
                'Employees already seeded (≥' . self::TARGET_COUNT . ') — skipping EmployeesTableSeeder.'
            );
            return;
        }

        $faker       = FakerFactory::create();
        $departments = ['Engineering', 'Marketing', 'HR', 'Sales', 'Support'];
        $now         = Carbon::now()->toDateTimeString();

        // ── 1. Build & bulk-insert 10,000 employee rows ───────────────────
        $this->command->info('Seeding ' . self::TARGET_COUNT . ' employees...');

        $employees = [];
        for ($i = 0; $i < self::TARGET_COUNT; $i++) {
            $employees[] = [
                'name'            => $faker->name,
                'email'           => 'employee_' . ($i + 1) . '_' . $faker->unique()->numberBetween(10000, 99999) . '@seeddata.local',
                'department'      => $faker->randomElement($departments),
                'role'            => $faker->jobTitle,
                'github_username' => $faker->userName . $faker->numberBetween(1, 9999),
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        // Chunk into 1,000-row batches to avoid packet-size limits
        foreach (array_chunk($employees, self::EMP_CHUNK) as $chunk) {
            Employee::insert($chunk);
        }
        unset($employees); // free memory

        $this->command->info(self::TARGET_COUNT . ' employees inserted.');

        // ── 2. Bulk-insert attendance & working hours ─────────────────────
        $this->command->info('Seeding attendance and working hours...');

        $statuses = [
            'Present', 'Present', 'Present', 'Present', 'Present',
            'Late', 'Late', 'Absent', 'Leave',
        ];

        // Build date range: last 14 weekdays
        $dates = [];
        for ($day = 14; $day >= 0; $day--) {
            $date = Carbon::now()->subDays($day);
            if (! $date->isWeekend()) {
                $dates[] = $date->toDateString();
            }
        }

        // Process employees in batches of ATT_BATCH to keep memory bounded
        $employeeIds = Employee::pluck('id')->toArray();

        foreach (array_chunk($employeeIds, self::ATT_BATCH) as $idBatch) {
            $attendances  = [];
            $workingHours = [];

            foreach ($idBatch as $employeeId) {
                foreach ($dates as $dateStr) {
                    $status = $statuses[array_rand($statuses)];

                    $attendances[] = [
                        'employee_id'     => $employeeId,
                        'attendance_date' => $dateStr,
                        'status'          => $status,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];

                    if ($status === 'Present') {
                        $workingHours[] = [
                            'employee_id'  => $employeeId,
                            'work_date'    => $dateStr,
                            'hours_worked' => round($faker->randomFloat(2, 7.5, 9.5), 2),
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ];
                    } elseif ($status === 'Late') {
                        $workingHours[] = [
                            'employee_id'  => $employeeId,
                            'work_date'    => $dateStr,
                            'hours_worked' => round($faker->randomFloat(2, 5.0, 7.5), 2),
                            'created_at'   => $now,
                            'updated_at'   => $now,
                        ];
                    }
                }
            }

            // Insert this batch of attendance & working-hour rows
            if (! empty($attendances)) {
                foreach (array_chunk($attendances, 2000) as $chunk) {
                    DB::table('attendances')->insert($chunk);
                }
            }
            if (! empty($workingHours)) {
                foreach (array_chunk($workingHours, 2000) as $chunk) {
                    DB::table('working_hours')->insert($chunk);
                }
            }

            unset($attendances, $workingHours);
        }

        $this->command->info(
            self::TARGET_COUNT . ' employees seeded with attendance and working hours.'
        );
    }
}
