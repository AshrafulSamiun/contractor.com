<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CountryController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => DB::table('countries')
                ->select(['id', 'country_name', 'iso_code', 'phone_code'])
                ->orderBy('country_name')
                ->get(),
        ]);
    }
}
