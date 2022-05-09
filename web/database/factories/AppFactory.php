<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\App;
use Faker\Generator as Faker;

$factory->define(App::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'gitlab_project_id' => $faker->numberBetween(1, 1000000),
        'primary_branch_name' => 'master',
        'namespace' => 'code',
        'repo_path' => "code/$faker->name"
    ];
});
