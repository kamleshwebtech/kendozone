<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Category;
use App\CategoryTournament;
use App\Tournament;
use App\TournamentLevel;
use App\User;
use Webpatser\Countries\Countries;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    $countries = Countries::all()->pluck('id')->toArray();

    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'grade_id' => $faker->numberBetween(1, 5),
        'country_id' => $faker->randomElement($countries),
        'city' => $faker->city,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'role_id' => $faker->numberBetween(1, 3),
        'verified' => true,
        'remember_token' => str_random(10),
        'provider' => '',
        'provider_id' => str_random(5)

    ];
});


$factory->define(App\Tournament::class, function (Faker\Generator $faker) {
    $users = User::all()->pluck('id')->toArray();
    $levels = TournamentLevel::all()->pluck('id')->toArray();

    return [
        'user_id' => $faker->randomElement($users),
        'name' => $faker->title,
        'date' => "2016-02-23",
        'registerDateLimit' => "2016-02-23",
        'cost' => $faker->numberBetween(10, 500),
        'sport' => 1,
        'type' => $faker->boolean(),
        'mustPay' => $faker->boolean(),
        'venue' => $faker->address,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'level_id' => $faker->randomElement($levels),
    ];
});


$factory->define(App\CategoryTournament::class, function (Faker\Generator $faker) {
    $tournaments = Tournament::all()->pluck('id')->toArray();
    $categories = Category::all()->pluck('id')->toArray();

    return [
        'tournament_id' => $faker->randomElement($tournaments),
        'category_id' => $faker->randomElement($categories),
    ];
});
$factory->define(App\CategoryTournamentUser::class, function (Faker\Generator $faker) {
    $tcs = CategoryTournament::all()->pluck('id')->toArray();
    $users = User::all()->pluck('id')->toArray();

    return [
        'category_tournament_id' => $faker->randomElement($tcs),
        'user_id' => $faker->randomElement($users),
        'confirmed' => $faker->numberBetween(0, 1),
    ];
});

