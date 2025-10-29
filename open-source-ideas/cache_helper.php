<?php
class SimpleCache {
    private $cacheDir = 'cache/';
    private $defaultTTL = 3600;
    public function __construct() {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    public function get($key) {
        $filename = $this->getCacheFilename($key);
        if (!file_exists($filename)) {
            return null;
        }
        $data = @unserialize(file_get_contents($filename));
        if ($data === false || !isset($data['expiry']) || $data['expiry'] < time()) {
            @unlink($filename);
            return null;
        }
        return $data['value'];
    }
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTTL;
        $filename = $this->getCacheFilename($key);
        $data = [
            'value' => $value,
            'expiry' => time() + $ttl
        ];
        return file_put_contents($filename, serialize($data)) !== false;
    }
    public function delete($key) {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            return @unlink($filename);
        }
        return true;
    }
    public function clear() {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
        return true;
    }
    private function getCacheFilename($key) {
        return $this->cacheDir . md5($key) . '.cache';
    }
}
function getCache() {
    static $cache = null;
    if ($cache === null) {
        $cache = new SimpleCache();
    }
    return $cache;
}
function cacheGet($key) {
    return getCache()->get($key);
}
function cacheSet($key, $value, $ttl = null) {
    return getCache()->set($key, $value, $ttl);
}
function cacheDelete($key) {
    return getCache()->delete($key);
}
function cacheClear() {
    return getCache()->clear();
}