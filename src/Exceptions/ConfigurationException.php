<?php

namespace Gangway\Laravel\Exceptions;

class ConfigurationException extends GangwayException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'configuration', 0);
    }
}
