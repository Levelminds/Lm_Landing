<?php
if (!function_exists('lm_config')) {
    function lm_config(?string $key = null, $default = null)
    {
        static $config;
        if ($config === null) {
            $config = require __DIR__ . '/../config/app.php';
        }

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}

if (!function_exists('lm_navigation_items')) {
    function lm_navigation_items(): array
    {
        static $items;
        if ($items === null) {
            $items = require __DIR__ . '/../config/navigation.php';
        }

        return $items;
    }
}

if (!function_exists('lm_view_path')) {
    function lm_view_path(string $view): string
    {
        $view = str_replace(['::', '.'], ['/', '/'], $view);
        return __DIR__ . '/../resources/views/' . $view . '.php';
    }
}

if (!function_exists('lm_view')) {
    function lm_view(string $view, array $data = []): string
    {
        $viewFile = lm_view_path($view);
        if (!file_exists($viewFile)) {
            throw new InvalidArgumentException("View '{$view}' not found at {$viewFile}");
        }

        extract($data, EXTR_OVERWRITE);

        ob_start();
        include $viewFile;

        return (string) ob_get_clean();
    }
}

if (!function_exists('lm_asset')) {
    function lm_asset(string $path): string
    {
        $base = rtrim(lm_config('base_url') ?? '', '/');
        $path = ltrim($path, '/');
        return ($base !== '' ? $base . '/' : '/') . $path;
    }
}

if (!function_exists('lm_route_is_active')) {
    function lm_route_is_active(string $href): bool
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if ($requestUri === '') {
            return false;
        }
        $parsed = parse_url($href, PHP_URL_PATH) ?: $href;
        return rtrim($parsed, '/') === rtrim($requestUri, '/');
    }
}
