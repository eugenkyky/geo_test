<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Country;
use DB;


class CountryController extends Controller
{
    public function post(Request $request)
    {
        if ($request->has('name')) {
            $country = Country::where('name', '=', $request->input('name'))->first();
            if ($country == null) {
                $country = new Country;
                $country->name = $request->input('name');
                $country->count = 0;
                $country->save();
                return response()->json(['id' => $country->id, 'name' => $country->name, 'count' => '0'])->setStatusCode(201);
            } else {
                return response()->setStatusCode(409, 'Resource already exists');
            }
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }

    public function put(Request $request, $id)
    {
        if ($request->has('name')) {
            $country = Country::where('id', '=', $id)->first();
            if ($country == null) {
                return response()->setStatusCode(404, 'Not Found');
            } else {
                $country->name = $request->input('name');
                $country->save();
                return response()->json(['id' => $country->id, 'name' => $country->name, 'count' => $country->count]);
            }
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }

    public function get($id)
    {
        $country = Country::where('id', '=', $id)->first();
        if ($country == null) {
            return response()->setStatusCode(404, 'Not Found');
        } else {
            return response()->json(['id' => $country->id, 'name' => $country->name, 'count' => $country->count]);
        }
    }

    public function getWithFilter(Request $request)
    {
        if ($request->has('name') OR $request->has('has_city_with_id') OR $request->has('has_city_with_name')) {
            $query_builder = DB::table('country');
            if ($request->has('name')) {
                $query_builder->where('country.name', '=', $request->input('name'));
            }
            if ($request->has('has_city_with_id')) {
                $query_builder->join('city', 'country.id', '=', 'city.country_id');
            }
            if ($request->has('has_city_with_name')) {
                $query_builder->where('city.name', '=', $request->input('has_city_with_name'));
            }
            $countries = $query_builder->select('country.*')->get();
            return response()->json($countries);
        } else {
            return response()->setStatusCode(400, 'Bad Request');
        }
    }
}