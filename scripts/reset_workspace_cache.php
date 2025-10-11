<?php

declare(strict_types=1);

/**
 * Simple workspace cache reset utility.
 *
 * This script clears a handful of well-known cache directories and rewrites
 * specific cache files with their default content so contributors can restore a
 * clean working state locally.
 */

$projectRoot = dirname(__DIR__);

$cacheDirectories = [
    $projectRoot . '/data/cache',
    $projectRoot . '/uploads/cache',
    $projectRoot . '/cache',
    $projectRoot . '/storage/cache',
    $projectRoot . '/vendor/cache',
];

$fileCaches = [
    $projectRoot . '/data/blogs.json' => [],
];

$result = [
    'cleared_directories' => [],
    'reset_files' => [],
    'errors' => [],
    'opcache_reset' => null,
];

foreach ($cacheDirectories as $directory) {
    if (!is_dir($directory)) {
        continue;
    }

    $error = clearDirectory($directory);

    if ($error === null) {
        $result['cleared_directories'][] = $directory;
        continue;
    }

    $result['errors'][] = [
        'target' => $directory,
        'message' => $error,
    ];
}

foreach ($fileCaches as $filePath => $defaultContent) {
    $encoded = json_encode($defaultContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($encoded === false) {
        $result['errors'][] = [
            'target' => $filePath,
            'message' => 'Unable to encode default content to JSON.',
        ];
        continue;
    }

    $directory = dirname($filePath);

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        $result['errors'][] = [
            'target' => $filePath,
            'message' => sprintf('Unable to create directory: %s', $directory),
        ];
        continue;
    }

    $bytesWritten = file_put_contents($filePath, $encoded . PHP_EOL);

    if ($bytesWritten === false) {
        $result['errors'][] = [
            'target' => $filePath,
            'message' => 'Unable to write default cache contents.',
        ];
        continue;
    }

    $result['reset_files'][] = $filePath;
}

if (function_exists('opcache_reset')) {
    $result['opcache_reset'] = @opcache_reset();
}

if ($result['errors'] !== []) {
    fwrite(STDERR, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    exit(1);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

/**
 * Recursively clears the contents of a directory.
 */
function clearDirectory(string $directory): ?string
{
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
    } catch (UnexpectedValueException $exception) {
        return $exception->getMessage();
    }

    /** @var SplFileInfo $item */
    foreach ($iterator as $item) {
        $path = $item->getPathname();

        if ($item->isDir()) {
            if (!@rmdir($path) && is_dir($path)) {
                return sprintf('Unable to remove directory: %s', $path);
            }
            continue;
        }

        if (!@unlink($path) && file_exists($path)) {
            return sprintf('Unable to delete file: %s', $path);
        }
    }

    return null;
}
