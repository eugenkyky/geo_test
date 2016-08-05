<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('/routes', 'RoutesController@getView'); //TODO add csrf protection dlya krasoty
    Route::post('/statistics', 'RoutesController@collectStatistic')->name('route.statistics'); //TODO add csrf protection dlya krasoty
});

//REST API
Route::post('/countries', 'CountryController@post')->name('country.post');
Route::put('/countries/{id}', 'CountryController@put')->where('id', '[0-9]+')->name('country.put');
Route::get('/countries/{id}', 'CountryController@get')->where('id', '[0-9]+')->name('country.get');
Route::get('/countries/search', 'CountryController@getWithFilter')->name('country.search');

Route::post('/cities', 'CityController@post')->name('city.post');
Route::put('/cities/{id}', 'CityController@put')->where('id', '[0-9]+')->name('city.put');
Route::get('/cities/{id}', 'CityController@get')->where('id', '[0-9]+')->name('city.get');
Route::get('/cities/search', 'CityController@getWithFilter')->name('city.search');

Route::put('/orders/{id}', 'OrderController@put')->where('id', '[0-9]+')->name('order.put');
Route::get('/orders/radius_search', 'OrderController@getWithCityRadius')->name('order.radius');
Route::post('/orders', 'OrderController@post')->name('order.post');
Route::get('/orders/{id}', 'OrderController@get')->where('id', '[0-9]+')->name('order.get');
Route::get('/orders/search', 'OrderController@getWithFilter')->name('order.search');
