<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Country;
use App\City;

class CityTest extends TestCase
{
    use DatabaseTransactions;

    public function testCreate()
    {
        $country = factory(App\Country::class)->create();

        $random_name = str_random(10);
        $response = $this->call('POST', route('city.post'), ['name' => $random_name, 'country_id' => $country->id ]);

        $this->assertEquals(201, $response->status());
        $result_array = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_array);

        $this->assertArrayHasKey('name', $result_array);
        $this->assertSame($random_name, $result_array['name']);

        $this->assertArrayHasKey('country_id', $result_array);
        $this->assertSame($country->id, $result_array['country_id']);

        $this->assertArrayHasKey('lat', $result_array);
        $this->assertSame( null, $result_array['lat']);

        $this->assertArrayHasKey('lng', $result_array);
        $this->assertSame( null, $result_array['lng']);
    }


    public function testUpdate()
    {
        $country = factory(App\Country::class)->create();
        $city = factory(App\City::class)->create(
            [
                'country_id' => $country->id
            ]
        );

        $random_name = str_random(10);

        $response = $this->call('PUT', route('city.put', $city->id), ['name' => $random_name]);
        $this->assertEquals(200, $response->status());

        $result_array = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_array);
        $this->assertSame($city->id, $result_array['id']);

        $this->assertArrayHasKey('name', $result_array);
        $this->assertSame($random_name, $result_array['name']);

        $this->assertArrayHasKey('country_id', $result_array);
        $this->assertSame( strval($country->id), $result_array['country_id']);

        $this->assertArrayHasKey('lat', $result_array);
        $this->assertSame( null, $result_array['lat']);

        $this->assertArrayHasKey('lng', $result_array);
        $this->assertSame( null, $result_array['lng']);

    }

    public function testGet()
    {
        $country = factory(App\Country::class)->create();
        $city = factory(App\City::class)->create(
            [
                'country_id' => $country->id
            ]
        );

        $response = $this->call('GET', route('city.get', $city->id ));
        $this->assertEquals(200, $response->status());

        $result_array = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_array);
        $this->assertSame($city->id, $result_array['id']);

        $this->assertArrayHasKey('name', $result_array);
        $this->assertSame($city->name, $result_array['name']);

        $this->assertArrayHasKey('country_id', $result_array);
        $this->assertSame( strval($city->country_id) , $result_array['country_id']);

        $this->assertArrayHasKey('lat', $result_array);
        $this->assertSame( null, $result_array['lat']);

        $this->assertArrayHasKey('lng', $result_array);
        $this->assertSame( null, $result_array['lng']);

    }

    public function testSearch()
    {
        $country = factory(App\Country::class)->create();
        $city = factory(App\City::class)->create(
            [
                'country_id' => $country->id
            ]
        );

        $response = $this->call('GET', route('city.search').'?name='.$city->name.
            '&has_country_with_id='.$city->country_id.
            '&has_country_with_name='.$country->name
        );

        $this->assertEquals(200, $response->status());

        $result_array = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_array[0]);
        $this->assertSame(strval($city->id), $result_array[0]['id']);

        $this->assertArrayHasKey('name', $result_array[0]);
        $this->assertSame($city->name, $result_array[0]['name']);

        $this->assertArrayHasKey('country_id', $result_array[0]);
        $this->assertSame(strval($city->country_id), $result_array[0]['country_id']);

        $this->assertArrayHasKey('lat', $result_array[0]);
        $this->assertSame( null, $result_array[0]['lat']);

        $this->assertArrayHasKey('lng', $result_array[0]);
        $this->assertSame( null, $result_array[0]['lng']);
    }
}
