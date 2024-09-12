<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Log;

class LogActions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {

        try{
            $user = auth()->user();
            $useragent = $request->userAgent();
            $ip = $request->header()['x-forwarded-for'] ?? 'unknown';
            // get name of the route
            $action = $request->route()->getName();

            Log::create([
                'user_id' => $user->id,
                'useragent' => $useragent,
                'ip' => $ip,
                'action' => $action,
            ]);
        } catch (\Exception $e) {
            // do nothing
        }

        return $next($request);
    }
}
