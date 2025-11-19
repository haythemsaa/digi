<?php

/**
 * API Rate Limiting Middleware
 * Limits requests per IP/user
 *
 * @author Pakiparc Team
 * @version 1.0
 */
class RateLimitMiddleware
{
    private static $limit = 100; // requests per minute
    private static $window = 60; // seconds

    public static function handle()
    {
        $key = self::getKey();

        if (self::isRateLimited($key)) {
            self::tooManyRequests();
        }

        self::incrementCounter($key);
    }

    /**
     * Get rate limit key (IP or user ID)
     */
    private static function getKey()
    {
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            return 'user_' . $userId;
        }

        return 'ip_' . $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Check if key is rate limited
     */
    private static function isRateLimited($key)
    {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key);

        if (!file_exists($cacheFile)) {
            return false;
        }

        $data = json_decode(file_get_contents($cacheFile), true);

        if ($data['timestamp'] < time() - self::$window) {
            // Window expired, reset
            unlink($cacheFile);
            return false;
        }

        return $data['count'] >= self::$limit;
    }

    /**
     * Increment request counter
     */
    private static function incrementCounter($key)
    {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key);

        if (!file_exists($cacheFile)) {
            $data = [
                'count' => 1,
                'timestamp' => time()
            ];
        } else {
            $data = json_decode(file_get_contents($cacheFile), true);

            if ($data['timestamp'] < time() - self::$window) {
                $data = [
                    'count' => 1,
                    'timestamp' => time()
                ];
            } else {
                $data['count']++;
            }
        }

        file_put_contents($cacheFile, json_encode($data));
    }

    /**
     * Return rate limit exceeded response
     */
    private static function tooManyRequests()
    {
        http_response_code(429);
        echo json_encode([
            'error' => true,
            'message' => 'Too many requests. Please try again later.'
        ]);
        exit;
    }
}
