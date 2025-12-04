<?php
if (!function_exists('getProjectBaseUrl')) {
    function getProjectBaseUrl(): string
    {
        static $baseUrl = null;
        if ($baseUrl !== null) {
            return $baseUrl;
        }

        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        if ($documentRoot !== '') {
            $documentRoot = str_replace('\\', '/', realpath($documentRoot));
        }

        $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));

        if ($documentRoot !== '' && strpos($projectRoot, $documentRoot) === 0) {
            $baseUrl = rtrim(str_replace($documentRoot, '', $projectRoot), '/');
        } else {
            $baseUrl = '';
        }

        return $baseUrl;
    }
}

if (!function_exists('resolveAssetPath')) {
    function resolveAssetPath(string $relativePath, string $cdnUrl): string
    {
        $normalizedPath = ltrim($relativePath, '/\\');
        $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPath);

        if (is_file($absolutePath)) {
            $baseUrl = getProjectBaseUrl();
            $prefix = $baseUrl !== '' ? $baseUrl . '/' : '/';
            return $prefix . str_replace('\\', '/', $normalizedPath);
        }

        return $cdnUrl;
    }
}
