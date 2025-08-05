<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BackofficeOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('pos_login')) {
            return $next($request);
        }
        abort(403, 'Backoffice access only');
    }
} 