<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AppFactory extends Factory
{
    public function definition ()
    {
        return [
            'name' => $this->faker->name,
            'gitlab_project_id' => $this->faker->numberBetween(1, 1000000),
            'primary_branch_name' => 'master',
            'namespace' => 'code',
            'repo_path' => "code/{$this->faker->name}"
        ];
    }
}
