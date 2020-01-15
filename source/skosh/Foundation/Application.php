<?php

namespace Skosh\Foundation;

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Cache.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Plugin.php');

class Application
{
    /**
     * Plugin config.
     *
     * @var array
     */
    private $root;

    /**
     * Plugin config.
     *
     * @var array
     */
    private $config;

    /**
     * Cache instance.
     *
     * @var \Skosh\Foundation\Cache
     */
    private $cache;

    /**
     * Initializer.
     */
    public function __construct()
    {
        $this->root = realpath(__DIR__ . '/../..');
        $this->config = include($this->root . '/.env.php');
        //$this->config = include($this->root.'/../public/.env.php'); // Development

        // Create cache instance
        $this->cache = new Cache($this->root);
        //$this->cache = new Cache($this->root.'/../public'); // Development
    }

    /**
     *  Return root path
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Sanitize value.
     *
     * @param array $value
     *
     * @return array
     */
    public function sanitize($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->sanitize($v);
            }

            return $value;
        }

        return addslashes(htmlspecialchars(strip_tags(trim($value))));
    }

    /**
     * This is the main function for Dispatcher,
     * it takes a request, parses it, finds the
     * best handler and calls it
     */
    public function run()
    {
        // Prevent XSS
        $request = $this->getRequest();

        // Find the best handler
        $handler = $this->getHandler($request);

        if ($handler) {
            // Caching for caching
            if ($time = $handler->getConfig('cache', 0)) {
                $this->cache->setTime($time);

                // Cache response
                $response = $this->cache->remember($handler->getCacheName($request), function () use ($handler, $request) {
                    return $handler->fire($this, $request);
                });
            }

            // Caching is disabled
            else {
                $response = $handler->fire($this, $request);
            }
        }
        else {
            header("HTTP/1.0 404 Not Found");
            $response = '404 Not Found';
        }

        echo $response;
    }

    /**
     * Find the best (aka most specific) handler for a request
     *
     * @param array $request
     *
     * @return string
     */
    protected function getHandler($request)
    {
        // Get service
        $service = empty($request['dispatch']) ? '' : $request['dispatch'];

        if ($this->config['services'] && isset($this->config['services'][$service])) {
            // Resolve service name
            $value = $this->resolve($service);

            // Plugin path
            $plugin_path = "{$this->root}/skosh/Plugins/{$value}/plugin.php";

            // Check for plugin
            if (file_exists($plugin_path)) {
                // Require plugin
                require_once($plugin_path);

                // Create new plugin instance
                $class = "\\Plugins\\{$value}\\Plugin";

                return new $class($this->config['services'][$service]);
            }
        }

        return false;
    }

    /**
     * Get sanitize user request.
     *
     * @return array
     */
    protected function getRequest()
    {
        return $this->sanitize($_REQUEST);
    }

    /**
     * Resolve a plugin instance from a dispatcher value.
     *
     * @param string $value
     *
     * @return string
     */
    protected function resolve($value)
    {
        $value = implode('_', explode('_', $value));
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }
}
