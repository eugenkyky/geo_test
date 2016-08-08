<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\City;
use App\Country;
use App\Order;
use DB;
use MatthiasMullie\Geo;
use GuzzleHttp;
use Log;

class OrderController extends Controller
{
    public function post(Request $request)
    {
        if ( $request->has('text') AND $request->has('city_id') AND $request->has('lat') AND $request->has('lng') ) {
            $city = City::where('id', '=', $request->input('city_id'))->first();
            if ($city){
                $order = new Order;
                $order->text = $request->input('text');
                $order->country_id = $city->country_id;
                $order->city_id = $request->input('city_id');
                $order->lat = $request->input('lat');
                $order->lng = $request->input('lng');
                $order->save();
                return response()->json(['id' => $order->id, 'text' => $order->text, 'country_id' => $order->country_id,
                    'lat' => $order->lat, 'lng' => $order->lng, 'city_id' => $order->city_id])->setStatusCode(201);

            } else {
                return response()->setStatusCode(409, 'Resource already exists');
            }

        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }

    public function put(Request $request, $id)
    {
        if ($request->has('text') OR $request->has('country_id') OR $request->has('city_id')) {
            $order = Order::where('id', '=', $id)->first();
            if ($order == null) {
                return response()->setStatusCode(404, 'Not Found');
            } else {
                if ($request->has('text')){
                    $order->text = $request->input('text');
                }
                if ($request->has('country_id') AND $request->has('city_id')) {
                    $city = City::where('id', '=', $request->has('city_id'))->where('country_id', '=', $request->has('country_id'))->first();
                    if ( $city == null ){
                        return response()->setStatusCode(404, 'Not Found');
                    } else {
                        $order->country_id = $request->input('country_id');
                        $order->city_id = $request->input('city_id');
                    }
                } else if ($request->has('city_id' AND ! $request->has('country_id'))) {
                    //найти просто city_id из Order
                    $city = City::where('id', '=', $request->has('city_id'))->where('country_id', '=', $order->country_id)->first();
                    if ( $city == null ){
                        return 'Error';
                    } else {
                        $order->city_id = $request->input('city_id');
                    }
                } else if (! $request->has('city_id') AND $request->has('country_id')) {
                    //Поменять страну без города нельзя.
                    return response()->setStatusCode(400, 'Bad Request');
                }

                $order->save();

                return response()->json(['id' => $order->id, 'text' => $order->text, 'country_id' => $order->country_id,
                    'lat' => $order->lat, 'lng' => $order->lng,'city_id' => $order->city_id]);
            }
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }

    public function get($id)
    {
        $order = Order::where('id', '=', $id)->first();
        if ($order == null) {
            return response()->setStatusCode(404, 'Not Found');
        } else {
            return response()->json(['id' => $order->id, 'text' => $order->text, 'country_id' => $order->country_id,
                'lat' => $order->lat, 'lng' => $order->lng, 'city_id' => $order->city_id]);
        }
    }

    public function getWithFilter(Request $request)
    {
        if ($request->has('text') OR $request->has('has_city_with_id') OR $request->has('has_city_with_name')
            OR $request->has('has_country_with_id') OR $request->has('has_country_with_name')){

            $query_builder = DB::table('order');

            if ($request->has('text')) {
                $query_builder->where('order.text', '=', $request->input('text'));
            }

            if ($request->has('has_city_with_id')) {
                $query_builder->where('city_id', '=', $request->input('has_city_with_id'));
            }

            if ($request->has('has_country_with_id')) {
                $query_builder->where('order.country_id', '=', $request->input('has_country_with_id'));
            }

            if ($request->has('has_city_with_name')) {
                $query_builder->join('city', 'city.id', '=', 'order.city_id')->where('city.name', '=', $request->input('has_city_with_name'));
            }

            if ($request->has('has_country_with_name')) {
                $query_builder->join('country', 'country.id', '=', 'order.country_id')->where('country.name', '=', $request->input('has_country_with_name'));
            }

            $countries = $query_builder->select('order.*')->get();

            return response()->json($countries);
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }

    public function getWithCityRadius(Request $request)
    {
        if ($request->has('city_id') AND $request->has('radius')) {
            $radius = $request->input('radius');
            $city = City::where('id', '=', $request->input('city_id'))->first();
            if ($city) {
                if ( ($city->lat == null) OR ( $city->long == null )){
                    $country = Country::where('id', '=', $city->country_id)->first();

                    $client = new GuzzleHttp\Client();
                    $res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address='.$city->name.'&components=administrative_area:'.$city->name.'|country:'.$country->name.'&key=AIzaSyDRznmJXKt96uYHIwNpFNNKpeqHo6WkvVQ');
                    $array = json_decode($res->getBody(), true);

                    $lat = null;
                    $lng = null;

                    if ( $array['status'] == 'OK' ){
                        foreach ($array['results'] as $result){
                            if ( $result['types'][0] == "locality" AND $result['types'][1] == "political" ){
                                $lat = $result['geometry']['location']['lat'];
                                $lng = $result['geometry']['location']['lng'];
                                break;
                            }
                        }
                    } else {
                        return response()->setStatusCode(500, 'Internal error');
                    }

                    //Сохранить
                    if ($lat != null and $lng != null){
                        $city->lat = $lat;
                        $city->lng = $lng;
                        $city->save();


                    } else {
                        //TODO log
                        return response()->setStatusCode(500, 'Internal error');
                    }
                }

                $geo = new Geo\Geo('km');
                $coord = new Geo\Coordinate($city->lat,  $city->lng );
                // calculate bounding box of 10km around this coordinate
                $bounds = $geo->bounds($coord, $radius);
                /*
                 * Now pass this on this the database, so it executes a query like:
                 *     SELECT *
                 *     FROM coordinates
                 *     WHERE
                 *         lat BETWEEN :swlat AND :nelat
                 *         lng BETWEEN :swlng AND :nelng
                 *
                 * :swlat being $bounds->sw->latitude
                 * :swlng being $bounds->sw->longitude
                 * :nelat being $bounds->ne->latitude
                 * :nelng being $bounds->ne->longitude
                 *
                 * Assume we have the database results in a variable called $results
                 */

                $orders = Order::whereBetween('lat', array($bounds->sw->latitude, $bounds->ne->latitude))->
                    whereBetween('lng', array($bounds->sw->longitude, $bounds->ne->longitude))->get()->toArray();

                    // now weed out entries that fit in the bounding box, but not exactly in
                    // the radius we want them to be in

                    foreach ($orders as $i => $result) {
                        $resultCoord = new Geo\Coordinate($result['lat'], $result['lng']);
                        // actual distance between source coordinate & result from DB
                        $distance = $geo->distance($coord, $resultCoord);
                        // if distance is too large, get rid of the result
                        if ($distance > $radius) {
                            unset($orders[$i]);
                        }
                    }

                return response()->json($orders);

            } else {
                return response()->setStatusCode(400, 'Bad Request');
            }
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }
}