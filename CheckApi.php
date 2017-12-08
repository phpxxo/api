<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Mockery\Exception;

class CheckApi
{
    private $appid = 'YFRUpoXifqqQLuM1';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $appid = $this->appid;

        try{
            if (!$request->hasHeader('token') || !$request->hasHeader('sign') || !$request->hasHeader('timestamp')){
                throw new \Exception('Request parameter Error');
            }

            $timestamp = $request->header('timestamp') / 1000;

            if ($timestamp < time() - 30){
                throw new \Exception('Request time wrong');
            }

            if ($timestamp > time() + 30){
                throw new \Exception('request has timed out');
            }

            $token = $request->header('token');
            $sign = $request->header('sign');

            if (!Cache::has($appid) || Cache::get($appid) != $token){
                throw new \Exception('Token validation failed');
            }

            $data = $request->all();
            $data['token'] = Cache::get($appid);

            ksort($data);
            $str = http_build_query($data);
            $getSign = strtoupper(sha1($str));

            if ($getSign != $sign){
                throw new \Exception('sign validation failed');
            }
        } catch (\Exception $e){
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage()
            ], 400);
        }

        Cache::forget($appid);

        return $next($request);
    }
}
