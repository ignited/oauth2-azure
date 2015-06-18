<?php

namespace Ignited\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

class Azure extends AbstractProvider
{
    public $scopes = ['Files.ReadWrite'];
    public $responseType = 'json';
    public $authorizationHeader = 'bearer';

    public function urlAuthorize()
    {
        return 'https://login.windows.net/common/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://login.windows.net/common/oauth2/token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.office.com/discovery/v2.0/me/services';
    }

    public function urlResourceDetails(AccessToken $token)
    {
        return 'https://api.office.com/discovery/v2.0/me/services';
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User([
            'email' => $response->email,
        ]);
        return $user;
    }

    public function resourceDetails($response, AccessToken $token)
    {
        $resource = [];

        foreach($response->value as $value)
        {
            if($value->capability == 'MyFiles' && $value->serviceApiVersion == 'v2.0')
            {
                $resource['files']['serviceEndpointUri'] = $value->serviceEndpointUri;
                $resource['files']['serviceResourceId'] = $value->serviceResourceId;
            }
        }

        return $resource;
    }

    public function getResourceDetails(AccessToken $token)
    {
        $response = $this->fetchResourceDetails($token);

        return $this->resourceDetails(json_decode($response), $token);
    }

    public function fetchResourceDetails(AccessToken $token)
    {
        $url = $this->urlResourceDetails($token);

        $headers = $this->getHeaders($token);

        return $this->fetchProviderData($url, $headers);
    }

}
