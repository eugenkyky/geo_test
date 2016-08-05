<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;


class CountryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreate()
    {
        $random_name = str_random(10);

        $response = $this->call('POST', route('country.post'), ['name' => $random_name ]);
        $this->assertEquals(201, $response->status());

        $result_array = json_decode( $response->content(), true);

        $this->assertArrayHasKey('id', $result_array);

        $this->assertArrayHasKey('name', $result_array);
        $this->assertSame($random_name, $result_array['name']);

        $this->assertArrayHasKey('count', $result_array);
        $this->assertSame('0', $result_array['count']);
    }

    public function testUpdate()
    {
        $country = factory(App\Country::class)->create();

        $random_name = str_random(10);

        $response = $this->call('PUT', route('country.put', $country->id), ['name' => $random_name]);
        $this->assertEquals(200, $response->status());

        $result_array = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_array);
        $this->assertSame($country->id, $result_array['id']);

        $this->assertArrayHasKey('name', $result_array);
        $this->assertSame($random_name, $result_array['name']);

        $this->assertArrayHasKey('count', $result_array);
        $this->assertSame('0', $result_array['count']);
    }

    public function testGet()
    {
        $country = factory(App\Country::class)->create();

        $response = $this->call('GET', route('country.get', $country->id ));
        $this->assertEquals(200, $response->status());

        $result_array = json_decode($response->content(), true);

        $this->assertArrayHasKey('id', $result_array);
        $this->assertSame($country->id, $result_array['id']);

        $this->assertArrayHasKey('name', $result_array);
        $this->assertSame($country->name, $result_array['name']);

        $this->assertArrayHasKey('count', $result_array);
        $this->assertSame(strval($country->count), $result_array['count']);
    }

    public function testSearch()
    {
        $country = factory(App\Country::class)->create();
        $city = factory(App\City::class)->create([  'country_id' => $country->id ]);

        $response = $this->call('GET', route('country.search').'?name='.$country->name.
                '&has_city_with_id='.$city->id.
                '&has_city_with_name='.$city->name);

        $this->assertEquals(200, $response->status());

        $result_array = json_decode($response->content(), true);
        $this->assertInternalType('array',$result_array);

        $this->assertArrayHasKey('id', $result_array[0]);
        $this->assertSame(strval($country->id), $result_array[0]['id']);

        $this->assertArrayHasKey('name', $result_array[0]);
        $this->assertSame($country->name, $result_array[0]['name']);

        $this->assertArrayHasKey('count', $result_array[0]);
        $this->assertSame(strval($country->count), $result_array[0]['count']);

    }
}
