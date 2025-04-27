<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if ($request->is('api/*')) {
                $notify[] = 'Unauthorized request. Please Login Again';
                return apiResponse('Unauthenticated! Please Login Again', 'error', $notify, statusCode: 401);
            }
            return route('user.login');
        }
    }
}
