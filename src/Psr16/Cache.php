<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2019 GameplayJDK
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Cache\Psr16;

use Cache\Cache as PhpCache;
use Cache\Exception\CacheException;
use Cache\Psr16\Exception\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

/**
 * Class Cache
 *
 * @package Cache\Psr16
 */
final class Cache implements CacheInterface
{
    /**
     * @var PhpCache
     */
    private $cache;

    /**
     * Cache constructor.
     * @param PhpCache $cache
     */
    public function __construct(PhpCache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $key = $this->sanitize($key);
        $value = null;

        try {
            $value = $this->cache->get($key);
        } catch (CacheException $exception) {
            throw new InvalidArgumentException();
        }

        return $value ?: $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        $key = $this->sanitize($key);

        try {
            $this->cache->set($key, $value);

            return true;
        } catch (CacheException $exception) {
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $key = $this->sanitize($key);

        try {
            $this->cache->del($key);

            return true;
        } catch (CacheException $exception) {
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        try {
            $this->cache->clr();

            return true;
        } catch (CacheException $exception) {
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_iterable($keys)) {
            throw new InvalidArgumentException("Key is not traversable!");
        }

        $values = [];

        foreach ($keys as $key) {
            $key = $this->sanitize($key);

            $values[] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!is_iterable($values)) {
            throw new InvalidArgumentException("Value is not traversable!");
        }

        $success = true;

        foreach ($values as $key => $value) {
            $key = $this->sanitize($key);

            if (!$this->set($key, $values, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        if (!is_iterable($keys)) {
            throw new InvalidArgumentException("Key is not traversable!");
        }

        $success = true;

        foreach ($keys as $key) {
            $key = $this->sanitize($key);

            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $key = $this->sanitize($key);
        $present = false;

        try {
            $present = $this->cache->has($key);
        } catch (CacheException $exception) {
        }

        return $present;
    }

    /**
     * @param string|null $key
     * @return string|null
     * @throws InvalidArgumentException
     */
    private function sanitize($key): ?string
    {
        // TODO: The pattern and replacement should be a constant.
        if (empty($key) || !is_string($key) || (null === ($clean = preg_replace('/[^a-zA-Z\-]/', '-', $key)))) {
            throw new InvalidArgumentException("Key '$key' is not a legal value!");
        }

        return $clean;
    }
}
