<?php

namespace App\Http\Controllers;

use LucaDegasperi\OAuth2Server\Authorizer;

class OauthController extends ApiController
{
    protected $authorizer;

    public function __construct(Authorizer $authorizer)
    {
        $this->authorizer = $authorizer;
    }

    public function postAccessToken()
    {
        return $this->authorizer->issueAccessToken();
    }
}
