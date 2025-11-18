<?php
/**
 * Utility Helper Functions
 */

class Utils {

    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'TND') {
        return number_format($amount, 2) . ' ' . $currency;
    }

    /**
     * Format date
     */
    public static function formatDate($date, $format = 'd/m/Y') {
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    public static function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        return date($format, strtotime($datetime));
    }

    /**
     * Time ago
     */
    public static function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return $diff . ' seconds ago';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' minutes ago';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' hours ago';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' days ago';
        } else {
            return date('d/m/Y', $timestamp);
        }
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename) {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        return $filename;
    }

    /**
     * Get file extension
     */
    public static function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Format file size
     */
    public static function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Calculate distance between two GPS coordinates (Haversine formula)
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Generate QR Code URL
     */
    public static function generateQRCode($data, $size = 200) {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
    }

    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }

    /**
     * Slug generator
     */
    public static function slug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Validate email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone
     */
    public static function isValidPhone($phone) {
        return preg_match('/^[0-9+\(\)\-\s]+$/', $phone);
    }

    /**
     * Generate color from string
     */
    public static function stringToColor($string) {
        $hash = md5($string);
        $color = '#';
        for ($i = 0; $i < 3; $i++) {
            $color .= substr($hash, $i * 2, 2);
        }
        return $color;
    }

    /**
     * Convert to JSON safely
     */
    public static function toJSON($data) {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Parse JSON safely
     */
    public static function fromJSON($json) {
        return json_decode($json, true);
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get client IP
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * Redirect with message
     */
    public static function redirectWithMessage($url, $message, $type = 'success') {
        $_SESSION[$type] = $message;
        header('Location: ' . APP_URL . '/' . $url);
        exit();
    }

    /**
     * Calculate fuel consumption
     */
    public static function calculateFuelConsumption($distance, $fuelUsed) {
        if ($distance == 0) return 0;
        return round(($fuelUsed / $distance) * 100, 2); // L/100km
    }

    /**
     * Calculate age from date
     */
    public static function calculateAge($birthDate) {
        $birth = new DateTime($birthDate);
        $today = new DateTime('today');
        return $birth->diff($today)->y;
    }
}
