<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AIQueryScope
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->roles()->where('name', 'owner')->exists()) {
            return response()->json(['answer' => 'Only owners can use the AI assistant.'], 403);
        }
        $question = strtolower($request->input('question', ''));
        // Block queries about other businesses/branches
        if (preg_match('/branch (i am not assigned|not my branch|not assigned to)/i', $question) ||
            preg_match('/business (other than mine|not my business)/i', $question)) {
            return response()->json(['answer' => 'You can only access your own business and assigned branches.'], 403);
        }
        return $next($request);
    }
} 