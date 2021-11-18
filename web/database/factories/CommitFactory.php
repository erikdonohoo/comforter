<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Commit;
use Faker\Generator as Faker;

$factory->define(Commit::class, function (Faker $faker) {
    $totalLines = $faker->numberBetween(1000, 2000);
    $totalCovered = $faker->numberBetween(999, $totalLines);
    $coverage = number_format($totalCovered / $totalLines, 4);
    return [
        'branch_name' => 'master',
        'sha' => $faker->sha256,
        'coverage' => $coverage,
        'total_lines' => $totalLines,
        'total_lines_covered' => $totalCovered
    ];
});
