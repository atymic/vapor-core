<?php

namespace Laravel\Vapor\Runtime;

use Aws\Ssm\SsmClient;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Support\Str;

class Secrets
{
    /**
     * Add all of the secret parameters at the given path to the environment.
     *
     * @param string     $path
     * @param array|null $parameters
     * @param string     $file
     *
     * @return array
     */
    public static function addToEnvironment($path, $parameters, $file)
    {
        if (!$parameters && file_exists($file)) {
            $parameters = require $file;
        }

        return tap(static::all($path, (array) $parameters), function ($variables) {
            self::setEnvironmentVariables($variables);
        });
    }

    /**
     * Get all of the secret parameters (AWS SSM) at the given path.
     *
     * @param string $path
     * @param array  $parameters
     *
     * @return array
     */
    public static function all($path, array $parameters = [])
    {
        if (empty($parameters)) {
            return [];
        }

        $ssm = SsmClient::factory([
            'region' => $_ENV['AWS_DEFAULT_REGION'],
            'version' => 'latest',
        ]);

        return collect($parameters)->chunk(10)->reduce(function ($carry, $parameters) use ($ssm, $path) {
            $ssmResponse = $ssm->getParameters([
                'Names' => collect($parameters)->map(function ($version, $parameter) use ($path) {
                    return $path . '/' . $parameter . ':' . $version;
                })->values()->all(),
                'WithDecryption' => true,
            ]);

            return array_merge($carry, static::parseSecrets(
                $ssmResponse['Parameters'] ?? []
            ));
        }, []);
    }

    /**
     * Parse the secret names and values into an array.
     *
     * @return array
     */
    protected static function parseSecrets(array $secrets)
    {
        return collect($secrets)->mapWithKeys(function ($secret) {
            $segments = explode('/', $secret['Name']);

            return [$segments[count($segments) - 1] => $secret['Value']];
        })->all();
    }

    protected static function setEnvironmentVariables(array $variables)
    {
        foreach ($variables as $key => $value) {
            if (Str::startsWith($key, 'DOT_ENV_')) {
                try {
                    $parsedDotEnv = Dotenv::parse($value);

                    self::setEnvironmentVariables($parsedDotEnv);

                } catch (InvalidFileException $e) {
                    echo "Failed to parse dot env secret [{$key}] into runtime." . PHP_EOL;
                }

                continue;
            }

            echo "Injecting secret [{$key}] into runtime." . PHP_EOL;

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
