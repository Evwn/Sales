<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PosOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (session('pos_login')) {
            return $next($request);
        }
        abort(403, 'POS access only');
    }
} 