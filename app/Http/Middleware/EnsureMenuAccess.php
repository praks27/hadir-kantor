<?php

namespace App\Http\Middleware;

use App\Services\MenuAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    public function handle(Request $request, Closure $next, string $menu): Response
    {
        abort_unless($request->user() && app(MenuAccessService::class)->allows($request->user(), $menu), 403);

        return $next($request);
    }
}
