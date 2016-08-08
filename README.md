HTTP API test assignment
=====================
## Установка
Для удобства лучше воспользоваться Vagrant. Для этого:
```bash
$ cd ~
$ mkdir test_assignment
$ cd test_assignment
$ wget 'https://raw.githubusercontent.com/eugenkyky/geo_test/master/deploy.sh'
$ wget 'https://raw.githubusercontent.com/eugenkyky/geo_test/master/Vagrantfile'
$ vagrant box add hashicorp/precise64
$ vagrant up
```

## Использование

После того как развернется окружение (код можно посмотреть в ```deploy.sh```) необходимо выполнить, что бы запустить тесты:

```bash
$ vagrant ssh
$ cd test_assignment
$ sudo nohup php -S 0.0.0.0:8000 web/index.php &
$ vendor/bin/phpunit tests/ 
```

На хосте можно обратиться к серверу и проверить работу первой части ТЗ, путем обращения в браузере к адресу ```127.0.0.1:4567/routes```
Код во вьюхе с названием Routes.php

## Описание

### HTTP API методы:

```php
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
```

Код в соответсвующих контроллерах. Для реализации метода "Получить все заказы которые находятся в радиусе от определенного города" использовал данную библиотеку: https://github.com/matthiasmullie/geo
Тестировал следующим образом(код в OrderTest.php):
1. Создается пара заявок около города Москва
2. И одна точка в Перми, вне города Москвы
3. Делается запрос на поиск по радиусу от Москвы
4. Проверка количества заявок
5. Запрос заявок в радиусе от Перми
6. Проверка количества заявок


#### В описании к вакансии был указан плюс
Плюсом будет опыт построения распределенных систем
Есть понимание как строятся такого типа системы: https://www.dropbox.com/s/eilhe6vgx9yok9u/Highload%20Architecture.docx?dl=0
