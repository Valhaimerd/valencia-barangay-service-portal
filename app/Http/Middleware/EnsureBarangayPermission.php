<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBarangayPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        abort_unless($user && $user->isBarangayOfficial(), 403);

        abort_unless(
            $user->canAccessBarangayPermission($permission),
            403,
            'You do not have permission to access this barangay module.'
        );

        return $next($request);
    }
}
