<?php

namespace App\Http\Controllers;
use App\Country;
use App\Stat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class RoutesController extends Controller
{
    public function getView()
    {
        $countries = Country::all();
        return view('Routes' , ['countries' => $countries]);
    }

    public function collectStatistic(Request $request)
    {
        $data = Input::all();
        $data = $data['points'];

        // сохраняем/обновляем
        foreach ($data as $point) {

            $country = Country::where('name', '=', $point['country_name'])->first();
            if ($country == null) {
                $country = new Country;
                $country->name = $point['country_name'];
                $country->count = 1;
                $country->save();
            } else {
                $country->count++;
                $country->save();
            }
        }

        // формируем ответ
        $response_data = array();
        $countries = Country::all();
        foreach ($countries as $country) {
            $response_data[] = array('country_name' => $country->name, 'count' => $country->count);
        }
        return response()->json($response_data);
    }
}