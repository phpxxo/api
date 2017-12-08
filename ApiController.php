<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ApiController extends Controller
{
    private $privateKey = 'Q9nBLlAj6EIgzsXn9yt0Pm6tfO54R4sD';

    // 生成token
    public function token(Request $request)
    {
        if (!$request->has('appid') || !$request->has('appsecret')){
            return response()->json([
                'code' => 400,
                'message' => 'Request parameter Error',
            ], 400);
        }

        $data = $request->all();
        $data['privateKey'] = $this->privateKey;
        $data['time'] = time();

        ksort($data);
        $str = http_build_query($data);
        $token = bcrypt($str);
        $expiresAt = Carbon::now()->addSeconds(30);

        Cache::put($data['appid'], $token, $expiresAt);

        return response()->json([
            'token' => $token,
            'expiresAt' => $expiresAt
        ]);
    }
}
