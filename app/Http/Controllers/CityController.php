<?php

namespace App\Http\Controllers;
use App\City;
use Illuminate\Http\Request;
use DB;
use Log;
use Validator;

class CityController extends Controller
{
    public function post(Request $request)
    {
        //process
        if ($request->has('name') AND $request->has('country_id')) {
            $city = City::where('name', '=', $request->input('name'))->where('country_id', '=', $request->input('country_id'))->first();
            if ($city == null) {
                $city = new City;
                $city->name = $request->input('name');
                $city->country_id = $request->input('country_id');
                $city->save();
                return response()->json(['id' => $city->id, 'name' => $city->name, 'country_id' => $city->country_id, 'lat' => null, 'lng' => null ])->setStatusCode(201);
            } else {
                return response()->setStatusCode(409, 'Resource already exists');
            }
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }

    public function put(Request $request, $id)
    {
        // Допускается изменить название и ссылку на страну
        if ( $request->has('name') OR $request->has('country_id')) {
            $city = City::where('id', '=', $id )->first();
            if ($city == null) {
                return response()->setStatusCode(404, 'Not Found');
            } else {
                if ( $request->has('name') ){
                    $city->name = $request->input('name');
                }
                if ( $request->has('country_id') ) {
                    $city->country_id = $request->input('country_id');
                }
                $city->save();
                return response()->json(['id' => $city->id, 'name' => $city->name, 'country_id' => $city->country_id, 'lat' => $city->lat, 'lng' => $city->lng ]);
            }
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }

    public function get($id)
    {
        $city = City::where('id', '=', $id)->first();
        if ($city == null) {
            return response()->setStatusCode(404, 'Not Found');
        } else {
            return response()->json(['id' => $city->id, 'name' => $city->name, 'country_id' => $city->country_id, 'lat' => $city->lat, 'lng' => $city->lng ]);
        }
    }

    public function getWithFilter(Request $request)
    {
        if ($request->has('name') OR $request->has('has_city_with_id') OR $request->has('has_city_with_name')) {
            $query_builder = DB::table('city');

            if ($request->has('name')) {
                $query_builder->where('city.name', '=', $request->input('name'));
            }

             if ($request->has('has_country_with_id')) {
                 $query_builder->where('country_id', '=', $request->input('has_country_with_id'));
             }

             if ($request->has('has_country_with_name_pattern')) {
                $query_builder->join('country', 'city.country_id', '=', 'country.id')
                     ->where('country.name', '=', $request->input('has_country_with_name'));
             }
            $cities = $query_builder->select('city.*')->get();
            return response()->json($cities);
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }
}