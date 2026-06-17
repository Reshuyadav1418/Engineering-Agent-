<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductivityScore;
use App\Models\Task;

echo "=== Productivity Scores Table ===\n";
echo "Total rows: " . ProductivityScore::count() . "\n\n";

$scores = ProductivityScore::select('employee_id', 'productivity_score', 'created_at', 'updated_at')->get();
foreach ($scores as $s) {
    echo "  emp_id={$s->employee_id}  score={$s->productivity_score}  created={$s->created_at}  updated={$s->updated_at}\n";
}

echo "\n=== DATE_FORMAT query test ===\n";
$trend = ProductivityScore::selectRaw(
    'DATE_FORMAT(updated_at, "%b %Y") as month_label,
     YEAR(updated_at) as yr,
     MONTH(updated_at) as mo,
     ROUND(AVG(productivity_score), 2) as avg_score'
)
->groupByRaw('yr, mo, month_label')
->orderByRaw('yr ASC, mo ASC')
->take(6)
->get();

echo "Trend rows: " . $trend->count() . "\n";
foreach ($trend as $row) {
    echo "  month={$row->month_label}  avg={$row->avg_score}\n";
}

echo "\n=== Tasks completed_date check ===\n";
echo "Total tasks: " . Task::count() . "\n";
echo "Completed tasks: " . Task::where('status','Completed')->count() . "\n";
echo "Completed with date: " . Task::where('status','Completed')->whereNotNull('completed_date')->count() . "\n";
