<?php

namespace Skosh\Foundation;

abstract class Plugin
{
    /**
     * Plugin config.
     *
     * @var array
     */
    private $config;

    /**
     * Initializer.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Return plugin name.
     *
     * @param array $request
     *
     * @return string
     */
    public function getCacheName($request = [])
    {
        // Dynamic caching based on request can
        // be done with get_class($this).implode($request)
        // in your plugin

        return get_class($this);
    }

    /**
     * Get a value from config.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfig($key, $default = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Fire request
     *
     * @param Application $app
     * @param array       $request
     *
     * @return mixed
     */
    abstract public function fire($app, array $request);
}
