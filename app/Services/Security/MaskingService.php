<?php

namespace App\Services\Security;

class MaskingService
{
    public function maskSecret(?string $value, int $keepStart = 4, int $keepEnd = 2): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $length = strlen($value);

        if ($length <= ($keepStart + $keepEnd)) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, $keepStart)
            .str_repeat('*', max(4, $length - ($keepStart + $keepEnd)))
            .substr($value, -$keepEnd);
    }

    public function maskHost(?string $host): string
    {
        $host = trim((string) $host);

        if ($host === '') {
            return '';
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $host);

            return "{$parts[0]}.{$parts[1]}.x.x";
        }

        $segments = explode('.', $host);

        if (count($segments) <= 2) {
            return $this->maskSecret($host, 2, 2);
        }

        return $segments[0].'.***.'.end($segments);
    }

    public function sanitizeFreeText(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return $text;
        }

        $patterns = [
            '/Bearer\s+[A-Za-z0-9\-\._~\+\/]+=*/i',
            '/(api[_\-\s]?key|password|token)\s*[:=]\s*[^\s,;]+/i',
        ];

        return preg_replace($patterns, '[REDACTED]', $text);
    }
}
