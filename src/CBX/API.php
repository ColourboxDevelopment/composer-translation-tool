<?php

namespace CBX;

class API
{
    private $url = null;
    private $cache = null;

    public function __construct($url, $cache) {
        $this->url = $url;
        $this->cache = $cache;
    }

    public function getURL() {
        return $this->url;
    }

    public function fetchConfig($language, $domain) {
        return $this->fetch("{$this->getURL()}/translation/config/{$language}/{$domain}");
    }

    public function fetchCollection($url) {
        $cacheKey = 'cbx-i18n-online-'.md5($url);
        $cachedData = $this->cache->get($cacheKey);
        if ($cachedData) {
            if (gettype($cachedData) === "string") {
                $json = json_decode(trim($cachedData), true);
                if (json_last_error() !== 0) {
                    throw new \Exception("I18NClass API Error. Collection JSON. ".json_last_error_msg());
                }
                return $json;
            } else {
                return $cachedData;
            }
        }
        $data = $this->fetch($url);
        if ($data) {
            $this->cache->set($cacheKey, gettype($data) === "string" ? $data : json_encode($data));
        }
        return $data;
    }

    private function fetch($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $result = curl_exec($ch);
        curl_close($ch);
        if (!$result) {
            throw new \Exception("I18NClass API Error. (CURL) ".curl_error($ch));
        }
        $json = json_decode(trim($result), true);
        if (json_last_error() !== 0) {
            throw new \Exception("I18NClass API Error. (JSON) ".json_last_error_msg());
        }
        return $json;
    }
}
