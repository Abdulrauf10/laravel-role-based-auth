<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsersResource;
use App\Models\IndonesiaCity;
use App\Models\IndonesiaProvince;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class MiscController extends Controller
{
    public function assesor(Request $request)
    {   
        $users = User::where('type','asesor')->paginate($request->offset ?? 20);
    
        return response([
            'http_code' => 200,
            'message' => 'Yeey, data found :D',
            'data' => new UsersResource($users)
        ],200);
    }

    public function verbatimer(Request $request)
    {
        $users = User::where('type','verbatimer')->paginate($request->offset ?? 20);
    
        return response([
            'http_code' => 200,
            'message' => 'Yeey, data found :D',
            'data' => new UsersResource($users)
        ],200);
    }

    public function getProvince(Request $request)
    {
        $cities = new IndonesiaProvince();

        if($request->q) {
            $cities = $cities->where('name','like','%'.$request->q.'%');
        }

        $cities = $cities->paginate($request->offset ?? 35);

        return response([
            'http_code' => 200,
            'message' => 'Yeey, data found',
            'data' => $cities
        ],200);
    }

    public function getCity(Request $request)
    {
        $request->validate([
            'province_code' => 'sometimes|nullable|exists:indonesia_provinces,code',
            'q' => 'sometimes|nullable'
        ]);

        $cities = new IndonesiaCity();

        if($request->province_code) {
            $cities = $cities->where('province_code',$request->province_code);
        }

        if($request->q) {
            $cities = $cities->where('name','like','%'.$request->q.'%');
        }

        $cities = $cities->paginate($request->offset ?? 20);

        return response([
            'http_code' => 200,
            'message' => 'Yeey, data found',
            'data' => $cities
        ],200);
    }

    
}
