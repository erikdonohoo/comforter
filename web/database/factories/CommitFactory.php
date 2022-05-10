<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CommitFactory extends Factory
{
    public function definition()
    {
        $totalLines = $this->faker->numberBetween(1000, 2000);
        $totalCovered = $this->faker->numberBetween(999, $totalLines);
        $coverage = number_format($totalCovered / $totalLines, 4);

        return [
            'branch_name' => 'master',
            'sha' => $this->faker->sha256,
            'coverage' => $coverage,
            'total_lines' => $totalLines,
            'total_lines_covered' => $totalCovered
        ];
    }
}
