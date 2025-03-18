<?php

namespace App\Console\Commands;

use Laravel\Octane\FrankenPhp\ServerProcessInspector;
use Laravel\Octane\FrankenPhp\ServerStateFile;
use Symfony\Component\Process\Process;

class StartFrankenPhpCommand extends \Laravel\Octane\Commands\StartFrankenPhpCommand
{
    protected $hidden = false;

    /**
     * Handle the command.
     *
     * @return int
     */
    public function handle(ServerProcessInspector $inspector, ServerStateFile $serverStateFile)
    {
        $this->ensureFrankenPhpWorkerIsInstalled();
        $this->ensurePortIsAvailable();

        $frankenphpBinary = $this->ensureFrankenPhpBinaryIsInstalled();

        if ($inspector->serverIsRunning()) {
            $this->components->error('FrankenPHP server is already running.');

            return 1;
        }

        $this->ensureFrankenPhpBinaryMeetsRequirements($frankenphpBinary);

        $this->writeServerStateFile($serverStateFile);

        $this->forgetEnvironmentVariables();

        $host = $this->getHost();
        $port = $this->getPort();

        $https = $this->option('https');

        $serverName = $https
            ? "https://$host:$port"
            : "http://:$port";

        $process = tap(new Process([
            $frankenphpBinary,
            'run',
            '-c', $this->configPath(),
        ], base_path(), [
            'APP_ENV' => app()->environment(),
            'APP_BASE_PATH' => base_path(),
            'APP_PUBLIC_PATH' => public_path(),
            'LARAVEL_OCTANE' => 1,
            'MAX_REQUESTS' => $this->option('max-requests'),
            'REQUEST_MAX_EXECUTION_TIME' => $this->maxExecutionTime(),
            'CADDY_GLOBAL_OPTIONS' => ($https && $this->option('http-redirect')) ? '' : 'auto_https disable_redirects',
            'CADDY_SERVER_ADMIN_PORT' => $this->adminPort(),
            'CADDY_SERVER_ADMIN_HOST' => $this->option('admin-host'),
            'CADDY_SERVER_LOG_LEVEL' => $this->option('log-level') ?: (app()->environment('local') ? 'INFO' : 'WARN'),
            'CADDY_SERVER_LOGGER' => 'json',
            'CADDY_SERVER_SERVER_NAME' => $serverName,
            'CADDY_SERVER_WORKER_COUNT' => $this->workerCount() ?: '',
            'CADDY_SERVER_EXTRA_DIRECTIVES' => $this->buildMercureConfig(),
            'WORDPRESS_CONTAINER_URL' => config('services.wordpress.container_url'),
            'WORDPRESS_HOST' => $host,
        ]));

        $server = $process->start();

        $serverStateFile->writeProcessId($server->getPid());

        return $this->runServer($server, $inspector, 'frankenphp');
    }
}
