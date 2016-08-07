<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

use App\Country;
use App\City;
use App\Order;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    protected function getLanLng( $array ){
        $lat = null;
        $lng = null;
        if ( $array['status'] == 'OK' ) {
            foreach ($array['results'] as $result) {
                $lat = $result['geometry']['location']['lat'];
                $lng = $result['geometry']['location']['lng'];
                break;
            }
        }
        return array('lat' => $lat , 'lng' => $lng );
    }

    public function testCreate()
    {
        $client = new GuzzleHttp\Client();
        $res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address=Kremlin&components=administrative_area:Moscow|country:Russia&key=AIzaSyDRznmJXKt96uYHIwNpFNNKpeqHo6WkvVQ');
        $array = json_decode($res->getBody(), true);

        $ll_array = $this->getLanLng($array);

        $country = factory(App\Country::class)->create([
            'name' => 'Russia',
        ]);

        $city = factory(App\City::class)->create(
            [
                'name' => 'Moscow',
                'country_id' => $country->id,
                'lat' => $ll_array['lat'],
                'lng' => $ll_array['lng']
            ]
        );

        // Создать заявку
        $response = $this->call('POST', route('order.post'), ['city_id' => $city->id, 'lat' => $ll_array['lat'], 'lng' => $ll_array['lng'], 'text' => 'Большой театр' ]);
        $this->assertEquals(201, $response->status());

        $result_post = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_post);
        $this->assertArrayHasKey('text', $result_post);

        $this->assertSame('Большой театр', $result_post['text'] );

        $this->assertArrayHasKey('city_id', $result_post);
        $this->assertSame($city->id, $result_post['city_id'] );

        $this->assertArrayHasKey('country_id', $result_post);
        $this->assertEquals($country->id, $result_post['country_id']);

        $this->assertArrayHasKey('lat', $result_post);
        $this->assertSame($ll_array['lat'], $result_post['lat'] );

        $this->assertArrayHasKey('lng', $result_post);
        $this->assertSame($ll_array['lng'], $result_post['lng'] );
    }

    public function testGet(){

        $country = factory(App\Country::class)->create([
            'name' => 'Russia',
        ]);

        $city = factory(App\City::class)->create(
            [
                'name' => 'Moscow',
                'country_id' => $country->id
            ]
        );

        $order = factory(App\Order::class)->create(
            [
                'city_id' => $city->id,
                'country_id' => $country->id
            ]
        );

        $response = $this->call('GET', route('order.get', $order->id ));

        $this->assertEquals(200, $response->status());
        $result_get = json_decode($response->content(), true);

        $this->assertArrayHasKey('id',$result_get);
        $this->assertEquals($order->id, $result_get['id']);

        $this->assertArrayHasKey('city_id', $result_get);
        $this->assertEquals($order->city_id, $result_get['city_id']);

        $this->assertArrayHasKey('country_id', $result_get);
        $this->assertEquals($order->country_id, $result_get['country_id']);

        $this->assertArrayHasKey('text',$result_get);
        $this->assertEquals($order->text, $result_get['text']);

        $this->assertArrayHasKey('lat',$result_get);
        $this->assertEquals($order->lat, $result_get['lat']);

        $this->assertArrayHasKey('lng',$result_get);
        $this->assertEquals($order->lng, $result_get['lng']);

    }

    public function testUpdate()
    {
        $country = factory(App\Country::class)->create([
            'name' => 'Russia',
        ]);

        $city = factory(App\City::class)->create(
            [
                'name' => 'Москва',
                'country_id' => $country->id
            ]
        );

        $order = factory(App\Order::class)->create(
            [
                'city_id' => $city->id,
                'country_id' => $country->id
            ]
        );

        $response = $this->call('PUT', route('order.put', $order->id),['text' => 'Updated text']);

        $this->assertEquals(200, $response->status());

        $result_put = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_put);
        $this->assertEquals($order->id, $result_put['id']);

        $this->assertArrayHasKey('city_id', $result_put);
        $this->assertEquals($order->city_id, $result_put['city_id']);

        $this->assertArrayHasKey('country_id', $result_put);
        $this->assertEquals($order->country_id, $result_put['country_id']);

        $this->assertArrayHasKey('text', $result_put);
        $this->assertEquals('Updated text', $result_put['text']);

        $this->assertArrayHasKey('lat', $result_put);
        $this->assertEquals($order->lat, $result_put['lat']);

        $this->assertArrayHasKey('lng', $result_put);
        $this->assertEquals($order->lng, $result_put['lng']);
    }

    public function testSearch()
    {
        $country = factory(App\Country::class)->create([
            'name' => 'Russia',
        ]);

        $city = factory(App\City::class)->create(
            [
                'name' => 'Moscow',
                'country_id' => $country->id
            ]
        );

        $order = factory(App\Order::class)->create(
            [
                'city_id' => $city->id,
                'country_id' => $country->id
            ]
        );

        $response = $this->call('GET', route('order.search').'?text='.$order->text.
            '&has_country_with_id='.$country->id.
            '&has_country_with_name='.$country->name.
            '&has_city_with_id='.$city->id.
            '&has_city_with_name='.$city->name
        );

        $this->assertEquals(200, $response->status());

        $result_array = json_decode($response->content(), true);

        $this->assertArrayHasKey('city_id', $result_array[0]);
        $this->assertSame(strval($city->id), $result_array[0]['city_id']);

        $this->assertArrayHasKey('text', $result_array[0]);
        $this->assertSame($order->text, $result_array[0]['text']);

        $this->assertArrayHasKey('country_id', $result_array[0]);
        $this->assertSame(strval($city->country_id), $result_array[0]['country_id']);

    }

    public function testRadius()
    {
        /*
         * Тест работает следующим образом:
         *  1. Создается пара заявок около города Москва(Большой театр уже должен быть создан к этому моменту)
         *  2. И одна точка в Перми, вне города Москвы
         *  3. Делается запрос на поиск по радиусу от Москвы
         *  4. Проверка количества заявок
         *  5. Запрос заявок в радиусе от Перми
         *  6. Проверка количества заявок
         */
        $country = factory(App\Country::class)->create([
            'name' => 'Russia',
        ]);
        $city = factory(App\City::class)->create(
            [
                'name' => 'Москва',
                'country_id' => $country->id
            ]
        );
        $city2 = factory(App\City::class)->create(
            [
                'name' => 'Пермь',
                'country_id' => $country->id
            ]
        );
        $created_orders_id = array();


        // 1 Создается пара заявок около города Москва
        //Большой театр
        //$response = $this->call('GET', );
        $client = new GuzzleHttp\Client();
        $res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address=Bolshoi%20Theater&components=administrative_area:Moscow|country:Russia&key=AIzaSyDRznmJXKt96uYHIwNpFNNKpeqHo6WkvVQ');
        $ll_array = $this->getLanLng(json_decode($res->getBody(), true));
        // Создать заявку
        $response = $this->call('POST', route('order.post'), ['city_id' => $city->id, 'lat' => $ll_array['lat'], 'lng' => $ll_array['lng'], 'text' => 'Большой театр' ]);

        $this->assertEquals(201, $response->status());

        $result_array = json_decode($response->content(), true);
        $created_orders_id[] = $result_array['id'];

        //---------------------------------------------------------------------------------------
        //Кремль
        $client = new GuzzleHttp\Client();

        $res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address=Kremlin&components=administrative_area:Moscow|country:Russia&key=AIzaSyDRznmJXKt96uYHIwNpFNNKpeqHo6WkvVQ');
        $ll_array = $this->getLanLng(json_decode($res->getBody(), true));
        // Создать заявку
        $response = $this->call('POST', route('order.post'), ['city_id' => $city->id, 'lat' => $ll_array['lat'], 'lng' => $ll_array['lng'], 'text' => 'Кремль' ]);
        $this->assertEquals(201, $response->status());

        $result_array = json_decode($response->content(), true);
        $created_orders_id[] = $result_array['id'];

        //-----------------------------------------------------------------------------------------------

        //2. И одна точка в Перми, вне города Москвы
        $client = new GuzzleHttp\Client();
        $res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address=Zvezda&components=administrative_area:Perm|country:Russia&key=AIzaSyDRznmJXKt96uYHIwNpFNNKpeqHo6WkvVQ');
        $ll_array = $this->getLanLng(json_decode($res->getBody(), true));

        // Создать заявку
        $response = $this->call('POST', route('order.post'), ['city_id' => $city2->id, 'lat' => $ll_array['lat'], 'lng' => $ll_array['lng'], 'text' => 'Серго' ]);
        $this->assertEquals(201, $response->status());

        $result_array = json_decode($response->content(), true);

        $created_orders_id[] = $result_array['id'];

        //3. Делается запрос на поиск по радиусу от Москвы
        $response = $this->call('GET', route('order.radius'), ['city_id' => $city->id, 'radius' => 1010]);
        $this->assertEquals(200, $response->status());
        $result_array = json_decode($response->content(), true);


        //4. Проверка количества заявок
        $this->assertEquals(2, count($result_array));

        $arr_ids = array_map(function($order) {
            return $order['id'];
        }, $result_array);

        // И их состав
        $this->assertEquals($created_orders_id[0], $arr_ids[0] );
        $this->assertEquals($created_orders_id[1], $arr_ids[1] );

        // 5. Запрос заявок в радиусе от Перми
        $response = $this->call('GET', route('order.radius'), ['city_id' => $city2->id, 'radius' => 100]);
        $this->assertEquals(200, $response->status()); //TODO здесь другой ответ
        $result_array = json_decode($response->content(), true);

        //6. Проверка количества заявок
        $this->assertEquals(1, count($result_array));

        $this->assertEquals($created_orders_id[2], $result_array[0]['id'] );

    }
}
