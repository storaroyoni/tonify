<?php

namespace App\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;
use Illuminate\Http\Request;

class LastfmProvider extends AbstractProvider
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    /**
     * Create a new provider instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $clientId
     * @param  string  $clientSecret
     * @param  string  $redirectUri
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUri)
    {
        // Pass all necessary parameters to the parent constructor
        parent::__construct($request, $clientId, $clientSecret, $redirectUri);

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $state
     * @return string
     */
    public function getAuthUrl($state)
    {
        return 'https://www.last.fm/api/auth?api_key=' . $this->clientId . '&state=' . $state;
    }

    /**
     * Get the access token from the provider.
     *
     * @param  string  $code
     * @return string
     */
    public function getAccessToken($code)
    {
        $response = $this->getHttpClient()->post('https://ws.audioscrobbler.com/2.0/', [
            'form_params' => [
                'method' => 'auth.getToken',
                'api_key' => $this->clientId,
                'api_sig' => $this->generateApiSig($code),
                'format' => 'json',
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        return $body['token'] ?? null;
    }

    /**
     * Retrieve the user information from the provider.
     *
     * @return \Laravel\Socialite\Two\User
     */
    public function user()
    {
        $token = $this->getAccessToken($this->request->get('code'));

        $response = $this->getHttpClient()->get('https://ws.audioscrobbler.com/2.0/', [
            'query' => [
                'method' => 'user.getInfo',
                'user' => 'username',  // Replace 'username' with actual user identifier
                'api_key' => $this->clientId,
                'api_sig' => $this->generateApiSig($token),
                'format' => 'json',
            ]
        ]);

        $user = json_decode($response->getBody(), true);

        return (new User)->setRaw($user)->map([
            'id' => $user['user']['id'],
            'name' => $user['user']['name'],
            'email' => $user['user']['email'],
        ]);
    }

    /**
     * Generate the API signature for authentication.
     *
     * @param  string  $token
     * @return string
     */
    protected function generateApiSig($token)
    {
        return md5($token . $this->clientSecret);
    }

    /**
     * Get the URL to fetch the access token.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return 'https://ws.audioscrobbler.com/2.0/';  // URL to retrieve the access token from
    }

    /**
     * Get the user by the access token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://ws.audioscrobbler.com/2.0/', [
            'query' => [
                'method' => 'user.getInfo',
                'user' => 'username',  // Replace 'username' with actual user identifier
                'api_key' => $this->clientId,
                'api_sig' => $this->generateApiSig($token),
                'format' => 'json',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the user data to Socialite's User object.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['user']['id'],
            'name' => $user['user']['name'],
            'email' => $user['user']['email'],
        ]);
    }
}
