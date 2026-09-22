<?php

namespace Gangway\Laravel\Resources;

use Gangway\Laravel\Client;
use Gangway\Laravel\Responses\ApiObject;

class Cms
{
    public function __construct(protected Client $client) {}

    public function catalog(): ApiObject
    {
        return $this->client->get('cms/catalog');
    }
}
