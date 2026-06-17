<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = \App\Models\ProductivityScore::count();
echo "Count: $count\n";

try {
    $scores = \App\Models\ProductivityScore::selectRaw(
        'DATE_FORMAT(updated_at, "%b %Y") as month_label,
         YEAR(updated_at)  as yr,
         MONTH(updated_at) as mo,
         ROUND(AVG(productivity_score), 2) as avg_score'
    )
    ->groupByRaw('yr, mo, month_label')
    ->orderByRaw('yr ASC, mo ASC')
    ->take(6)
    ->get();

    print_r($scores->toArray());
} catch (\Exception $e) {
    echo "Query Error: " . $e->getMessage() . "\n";
}
