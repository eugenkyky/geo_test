<?php

$factory->define(App\City::class, function (Faker\Generator $faker) {
    return [
        'name' => str_random(10),
        'country_id' => 1,
        'lat' => null,
        'lng' => null,
    ];
});
