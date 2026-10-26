<?php

namespace ME\Retail\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ME\Retail\Support\RetailModules;

/**
 * Blocks every Retail route that belongs to a switched-off feature (see the
 * `routes` lists in Config/features.php), so a hidden menu item can't still be
 * reached by URL.
 */
class EnsureRetailFeature
{
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()?->getName();

        if (!RetailModules::routeEnabled($routeName)) {
            $feature = RetailModules::definitions()[RetailModules::featureForRoute($routeName)]['label'] ?? '';

            if ($request->expectsJson()) {
                return response()->json(['message' => __(':feature is turned off in Retail settings.', ['feature' => __($feature)])], 403);
            }

            abort(403, __(':feature is turned off in Retail settings.', ['feature' => __($feature)]));
        }

        return $next($request);
    }
}
