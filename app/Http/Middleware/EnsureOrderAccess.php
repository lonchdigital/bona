<?php

namespace App\Http\Middleware;

use App\Models\Order;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');
        $isOwner = $order instanceof Order
            && $request->user()
            && (int) $order->user_id === (int) $request->user()->getAuthIdentifier();

        abort_unless($isOwner || $request->hasValidSignature(), 403);

        return $next($request);
    }
}
