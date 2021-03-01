<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Commit;
use Faker\Generator as Faker;

$factory->define(Commit::class, function (Faker $faker) {
    $totalLines = $faker->numberBetween(1000, 2000);
    return [
        'branch_name' => 'master',
        'sha' => $faker->sha256,
        'coverage' => $faker->randomFloat(config('app.coverageToDecimalPoint'), 0, 99),
        'total_lines' => $totalLines,
        'total_lines_covered' => $faker->numberBetween(999, $totalLines)
    ];
});
