<?php namespace Skosh\Foundation;

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
     */
    public function __construct(array $config)
    {
        $this->config  = $config;
    }

    /**
     * Return plugin name.
     *
     * @return string
     */
    public function getCacheName($request = array())
    {
        // Dynamic caching based on request can
        // be done with get_class($this).implode($request)
        // in your plugin

        return get_class($this);
    }

    /**
     * Get a value from config.
     *
     * @param  string $key
     * @param  mixed  $default
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
     * @return mixed
     */
    abstract public function fire($app, array $request);
}
