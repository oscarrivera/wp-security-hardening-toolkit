<?php

declare(strict_types=1);

namespace OrHardening;

final class RemoveVersionLeak
{
    public function generator(string $markup): string
    {
        unset($markup);

        return '';
    }

    public function stripWpVer(string $src, string $wpVersion): string
    {
        if ($src === '' || $wpVersion === '') {
            return $src;
        }

        $ver = $this->queryParam($src, 'ver');
        if ($ver === null || $ver !== $wpVersion) {
            return $src;
        }

        return $this->withoutQueryParam($src, 'ver');
    }

    private function queryParam(string $src, string $name): ?string
    {
        $query = parse_url($src, PHP_URL_QUERY);
        if (!is_string($query) || $query === '') {
            return null;
        }
        parse_str($query, $params);
        if (!array_key_exists($name, $params) || !is_scalar($params[$name])) {
            return null;
        }

        return (string) $params[$name];
    }

    private function withoutQueryParam(string $src, string $name): string
    {
        $fragment = '';
        $hashPos = strpos($src, '#');
        if ($hashPos !== false) {
            $fragment = substr($src, $hashPos);
            $src = substr($src, 0, $hashPos);
        }

        $qPos = strpos($src, '?');
        if ($qPos === false) {
            return $src . $fragment;
        }

        $path = substr($src, 0, $qPos);
        parse_str(substr($src, $qPos + 1), $params);
        unset($params[$name]);
        $qs = http_build_query($params);

        return $path . ($qs !== '' ? '?' . $qs : '') . $fragment;
    }
}
