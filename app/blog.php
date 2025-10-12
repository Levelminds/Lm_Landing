<?php
declare(strict_types=1);

if (!function_exists('lm_decode_blog_html')) {
    function lm_decode_blog_html($value): string
    {
        return trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

if (!function_exists('lm_decode_blog_text')) {
    function lm_decode_blog_text($value): string
    {
        return trim(strip_tags(lm_decode_blog_html($value)));
    }
}

if (!function_exists('lm_format_blog_date')) {
    function lm_format_blog_date($value): string
    {
        if (empty($value)) {
            return '—';
        }

        try {
            $date = new DateTime((string) $value);
            return $date->format('M j, Y');
        } catch (Throwable $exception) {
            return '—';
        }
    }
}

if (!function_exists('lm_normalise_category')) {
    function lm_normalise_category($value): string
    {
        $normalised = strtolower(trim((string) $value));
        $mapping = [
            'teacher' => 'teachers',
            'teachers' => 'teachers',
            'school' => 'schools',
            'schools' => 'schools',
            'general' => 'general',
        ];

        return $mapping[$normalised] ?? 'general';
    }
}

if (!function_exists('lm_blog_category_label')) {
    function lm_blog_category_label(string $category): string
    {
        switch (lm_normalise_category($category)) {
            case 'teachers':
                return 'Teachers';
            case 'schools':
                return 'Schools';
            default:
                return 'General';
        }
    }
}

if (!function_exists('lm_blog_excerpt')) {
    function lm_blog_excerpt(array $post, int $limit = 160): string
    {
        $text = lm_decode_blog_text($post['summary'] ?? '');
        if ($text === '') {
            $text = lm_decode_blog_text($post['content'] ?? '');
        }
        if ($text === '') {
            return '';
        }

        $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($length > $limit) {
            $slice = function_exists('mb_substr')
                ? mb_substr($text, 0, $limit - 1, 'UTF-8')
                : substr($text, 0, $limit - 1);
            return rtrim($slice) . '…';
        }

        return $text;
    }
}

if (!function_exists('lm_blog_read_time')) {
    function lm_blog_read_time(array $post): string
    {
        $content = lm_decode_blog_html($post['content'] ?? '');
        if ($content === '') {
            $content = lm_decode_blog_html($post['summary'] ?? '');
        }
        if ($content === '') {
            return '';
        }

        $words = str_word_count(strip_tags($content));
        if ($words === 0) {
            return '';
        }

        $minutes = max(1, (int) ceil($words / 200));
        return sprintf('%d min read', $minutes);
    }
}

if (!function_exists('lm_blog_media_url')) {
    function lm_blog_media_url(array $post): string
    {
        $url = trim((string) ($post['media_url'] ?? ''));
        if ($url !== '') {
            return $url;
        }

        return 'assets/images/img-1-min.jpg';
    }
}

if (!function_exists('lm_blog_share_url')) {
    function lm_blog_share_url(int $postId, ?string $baseUrl = null): string
    {
        if ($baseUrl === null) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $hostName = $_SERVER['HTTP_HOST'] ?? 'www.levelminds.in';
            $baseUrl = sprintf('%s://%s', $scheme, $hostName);
        }

        return rtrim($baseUrl, '/') . '/blogs.php?post=' . $postId;
    }
}

if (!function_exists('lm_fetch_blog_posts')) {
    function lm_fetch_blog_posts(PDO $pdo): array
    {
        try {
            $columns = 'id, title, author, summary, content, media_type, media_url, created_at, views, likes, category, status';
            $statement = $pdo->prepare(
                "SELECT {$columns} FROM blog_posts WHERE LOWER(status) IN ('published', 'approved') ORDER BY created_at DESC"
            );
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $exception) {
            throw new RuntimeException('Unable to load blog posts: ' . $exception->getMessage(), 0, $exception);
        }
    }
}

if (!function_exists('lm_partition_blog_posts')) {
    function lm_partition_blog_posts(array $posts): array
    {
        $featured = $posts[0] ?? null;
        $buckets = [
            'teachers' => [],
            'schools' => [],
            'general' => [],
        ];

        foreach ($posts as $index => $post) {
            if ($index === 0) {
                continue;
            }
            $buckets[lm_normalise_category($post['category'] ?? 'general')][] = $post;
        }

        return [$featured, $buckets];
    }
}
