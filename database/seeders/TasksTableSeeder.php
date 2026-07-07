<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;
use Carbon\Carbon;

class TasksTableSeeder extends Seeder
{
    /**
     * Total tasks to seed.
     * In production (Render) we use a small seed so the server starts fast.
     * Locally you can override with SEED_TASK_COUNT=180000 in your .env.
     */
    private function targetCount(): int
    {
        return (int) env('SEED_TASK_COUNT', app()->isProduction() ? 500 : 5000);
    }

    /**
     * Rows per DB::table('tasks')->insert() call.
     */
    private const CHUNK_SIZE = 500;

    /**
     * Pre-generated pool sizes — reusing generated strings avoids repeated
     * Faker overhead for 180 k rows.
     */
    private const TITLE_POOL = 2000;
    private const DESC_POOL  = 500;

    /**
     * Run the task seeds.
     *
     * Idempotent: if target count or more tasks already exist the seeder is
     * skipped entirely, so `php artisan db:seed --force` is safe to repeat.
     *
     * Tasks are randomly distributed across all employees, with assigned_date
     * and completed_date within 1 June – 18 June 2026.
     */
    public function run(): void
    {
        $targetCount = $this->targetCount();

        // ── Idempotency guard ─────────────────────────────────────────────
        if (Task::count() >= $targetCount) {
            $this->command->info(
                'Tasks already seeded (≥' . $targetCount . ') — skipping TasksTableSeeder.'
            );
            return;
        }

        // ── Employee IDs ──────────────────────────────────────────────────
        $employeeIds = Employee::pluck('id')->toArray();
        if (empty($employeeIds)) {
            $this->command->warn('No employees found — skipping TasksTableSeeder.');
            return;
        }

        $faker    = FakerFactory::create();
        $statuses = ['Pending', 'In Progress', 'Completed'];
        $now      = Carbon::now()->toDateTimeString();
        $empCount = count($employeeIds);

        // Date range: 1 June – 18 June 2026 (Unix timestamps)
        $startTs = Carbon::create(2026, 6, 1, 0, 0, 0)->timestamp;
        $endTs   = Carbon::create(2026, 6, 18, 23, 59, 59)->timestamp;

        // ── Pre-build string pools to avoid 180k Faker calls ─────────────
        $this->command->info('Building title and description pools...');

        $titlePool = [];
        for ($t = 0; $t < self::TITLE_POOL; $t++) {
            $titlePool[] = $faker->sentence(random_int(4, 8));
        }

        $descPool = [];
        for ($d = 0; $d < self::DESC_POOL; $d++) {
            $descPool[] = $faker->sentence(random_int(10, 18));
        }

        $titleMax = self::TITLE_POOL - 1;
        $descMax  = self::DESC_POOL  - 1;
        $empMax   = $empCount - 1;

        // ── Bulk-insert in chunks ─────────────────────────────────────────
        $this->command->info('Seeding ' . $targetCount . ' tasks in chunks of ' . self::CHUNK_SIZE . '...');

        $batch    = [];
        $inserted = 0;

        for ($i = 0; $i < $targetCount; $i++) {
            $status = $statuses[array_rand($statuses)];

            // Random assigned_date within June 1–18
            $assignedTs   = random_int($startTs, $endTs);
            $assignedDate = date('Y-m-d', $assignedTs);

            // completed_date is only set for Completed tasks; must be ≥ assigned_date
            $completedDate = null;
            if ($status === 'Completed') {
                $completedTs   = random_int($assignedTs, $endTs);
                $completedDate = date('Y-m-d', $completedTs);
            }

            $batch[] = [
                'employee_id'     => $employeeIds[random_int(0, $empMax)],
                'title'           => $titlePool[random_int(0, $titleMax)],
                'description'     => $descPool[random_int(0, $descMax)],
                'status'          => $status,
                'assigned_date'   => $assignedDate,
                'completed_date'  => $completedDate,
                'estimated_hours' => round(mt_rand(100, 4000) / 100, 2), // 1.00 – 40.00
                'actual_hours'    => $status === 'Completed'
                    ? round(mt_rand(100, 4000) / 100, 2)
                    : 0.00,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];

            // Flush when chunk is full
            if (count($batch) === self::CHUNK_SIZE) {
                DB::table('tasks')->insert($batch);
                $inserted += self::CHUNK_SIZE;
                $batch     = [];
                if ($inserted % 500 === 0) {
                    $this->command->info("  → {$inserted} / {$targetCount} tasks inserted...");
                }
            }
        }

        // Insert any remaining rows
        if (! empty($batch)) {
            DB::table('tasks')->insert($batch);
            $inserted += count($batch);
        }

        $this->command->info("✓ {$inserted} tasks seeded successfully.");
    }
}
