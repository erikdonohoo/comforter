<?php

namespace App\Http\Middleware;

use Closure;
use Omines\OAuth2\Client\Provider\Gitlab;

/**
 * GitlabOauthMiddleware classs
 *
 * @property Gitlab $gitlab
 */
class GitlabOauthMiddleware
{

    public function __construct(Gitlab $gitlab)
    {
        $this->gitlab = $gitlab;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->query('code')) {
            $authUrl = $this->gitlab->getAuthorizationUrl();
            session(['oauth2state' => $this->gitlab->getState()]);
            return redirect($authUrl);
        }

        return $next($request);
    }
}
