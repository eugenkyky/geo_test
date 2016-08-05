<?php

$factory->define(App\Order::class, function (Faker\Generator $faker) {
    return [
        'text' => str_random(10),
        'country_id' => 1,
        'city_id' => 1,
        'lat' => null,
        'lng' => null,
    ];
});