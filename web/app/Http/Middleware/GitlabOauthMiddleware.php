<?php

namespace App\Http\Middleware;

use Closure;
use Omines\OAuth2\Client\Provider\Gitlab;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
        if (session('gitlabToken')) {
            return $next($request);
        }

        if (!$request->query('code')) {
            // TODO: Locally we need a URL that works in the browser
            // but also in the backend to communicate with the API
            // and unfortunately they are 2 different urls
            // http://localhost:4000 (browser) and http://gitlab:4000 (network)
            $authUrl = $this->gitlab->getAuthorizationUrl();
            session(['oauth2state' => $this->gitlab->getState()]);
            session(['gitlabToken' => null]);
            return redirect($authUrl);
        } else if (!$request->query('state') || $request->query('state') !== session('oauth2state')) {
            session(['oauth2state' => null]);
            session(['gitlabToken' => null]);
            throw new UnauthorizedHttpException('Bad');
        } else {

            // Get token
            $token = $this->gitlab->getAccessToken('authorization_code', [
                'code' => $request->query('code')
            ]);

            $request->query->set('code', null);

            session(['gitlabToken' => $token]);
        }

        return $next($request);
    }
}
