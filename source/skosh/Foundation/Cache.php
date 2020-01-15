<?php

namespace Skosh\Foundation;

use Closure;

class Cache
{
    /**
     * The file cache directory
     *
     * @var string
     */
    protected $directory;

    /**
     * Length of time to cache a file (in seconds)
     *
     * @var int
     */
    public $time = 0;

    /**
     * Initializer.
     *
     * @param string $directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory . DIRECTORY_SEPARATOR . 'cache';
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param int $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string   $key
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function remember($key, Closure $callback)
    {
        // If the item exists in the cache we will just return this immediately
        // otherwise we will execute the given Closure and cache the result
        // of that execution for the given number of minutes in storage.
        if (is_null($value = $this->get($key)) === false) {
            return $value;
        }

        $this->put($key, $value = $callback());

        return $value;
    }

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function put($key, $value)
    {
        $value = serialize($value);

        $this->createCacheDirectory($path = $this->path($key));

        file_put_contents($path, $value);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if ($this->has($key)) {
            $contents = file_get_contents($this->path($key));

            return unserialize($contents);
        }

        return null;
    }

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        $path = $this->path($key);

        return (file_exists($path) && (filemtime($path) + $this->time >= time()));
    }

    /**
     * Create the file cache directory if necessary.
     *
     * @param string $path
     *
     * @return void
     */
    protected function createCacheDirectory($path)
    {
        $dir = dirname($path);

        if (is_dir($dir) === false) {
            mkdir($dir, 0777, true);
        }
    }

    /**
     * Get the full path for the given cache key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function path($key)
    {
        $parts = array_slice(str_split($hash = md5($key), 2), 0, 2);

        return $this->directory . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . $hash;
    }
}
