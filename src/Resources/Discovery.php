<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Client;
use Gangway\Laravel\Responses\ApiObject;

class Discovery
{
    public function __construct(protected Client $client) {}

    public function ping(): ApiObject
    {
        return $this->client->get('ping');
    }

    public function me(): ApiObject
    {
        return $this->client->get('me');
    }

    public function capabilities(): ApiObject
    {
        return $this->client->get('capabilities');
    }
}
