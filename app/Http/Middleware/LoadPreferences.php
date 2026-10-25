<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class LoadPreferences
{
    public function handle(Request $request, Closure $next): Response
    {
        $preferences = Setting::whereIn('key', ['default_weight_unit', 'currency_label'])->pluck('value', 'key');
        View::share('defaultUnit', $preferences['default_weight_unit'] ?? 'lb');
        View::share('currency', $preferences['currency_label'] ?? 'BDT');

        return $next($request);
    }
}
