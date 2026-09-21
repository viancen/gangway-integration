<?php

namespace Gangway\Laravel\Console;

use Gangway\Laravel\Exceptions\GangwayException;
use Gangway\Laravel\Facades\Gangway;
use Illuminate\Console\Command;

class PingCommand extends Command
{
    protected $signature = 'gangway:ping';

    protected $description = 'Verify the configured Gangway operator API key';

    public function handle(): int
    {
        try {
            $pong = Gangway::ping();
            $me = Gangway::me();
        } catch (GangwayException $exception) {
            $this->error($exception->getMessage());

            if ($exception->requestId) {
                $this->line('Request ID: '.$exception->requestId);
            }

            return self::FAILURE;
        }

        $operator = is_array($me['operator'] ?? null) ? $me['operator'] : [];
        $key = is_array($me['api_key'] ?? null) ? $me['api_key'] : [];

        $this->info('Connected to Gangway.');
        $this->line('API version: '.($pong['api_version'] ?? 'unknown'));
        $this->line('Platform: '.($pong['platform_version'] ?? 'unknown'));
        $this->line('Operator: '.($operator['name'] ?? 'unknown').' ('.($operator['slug'] ?? '-').')');
        $this->line('Key: '.($key['name'] ?? 'unknown').' ['.($key['prefix'] ?? '').']');

        return self::SUCCESS;
    }
}
