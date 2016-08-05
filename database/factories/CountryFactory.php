<?php
$factory->define(App\Country::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(10),
        'count' => 0,
    ];
});