<?php

namespace App\Http\Middleware;

use DB;
use Request;
use Closure;
use App\Exceptions\UnauthorizedClientException;

class OauthCheckClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $clientId     = Request::input('client_id');
        $clientSecret = Request::input('client_secret');

        $client = DB::table('oauth_clients')
                ->where('id', $clientId)
                ->where('secret', $clientSecret)
                ->get();

        if (empty($client)) {
            throw new UnauthorizedClientException('Unauthorized client.');
        }

        return $next($request);
    }
}