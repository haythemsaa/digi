<?php
/**
 * Notification Helper Class
 * For managing in-app notifications
 */

class Notification extends Database {

    /**
     * Create notification
     */
    public static function create($userId, $type, $category, $title, $message, $link = null) {
        $db = new Database();

        $db->query('INSERT INTO notifications (user_id, type, category, title, message, link)
                    VALUES (:user_id, :type, :category, :title, :message, :link)');

        $db->bind(':user_id', $userId);
        $db->bind(':type', $type);
        $db->bind(':category', $category);
        $db->bind(':title', $title);
        $db->bind(':message', $message);
        $db->bind(':link', $link);

        return $db->execute();
    }

    /**
     * Get user notifications
     */
    public static function getUserNotifications($userId, $unreadOnly = false) {
        $db = new Database();

        $sql = 'SELECT * FROM notifications WHERE user_id = :user_id';

        if ($unreadOnly) {
            $sql .= ' AND `read` = 0';
        }

        $sql .= ' ORDER BY created_at DESC LIMIT 50';

        $db->query($sql);
        $db->bind(':user_id', $userId);

        return $db->fetchAll();
    }

    /**
     * Mark as read
     */
    public static function markAsRead($notificationId) {
        $db = new Database();

        $db->query('UPDATE notifications SET `read` = 1, read_at = NOW() WHERE id = :id');
        $db->bind(':id', $notificationId);

        return $db->execute();
    }

    /**
     * Mark all as read
     */
    public static function markAllAsRead($userId) {
        $db = new Database();

        $db->query('UPDATE notifications SET `read` = 1, read_at = NOW() WHERE user_id = :user_id AND `read` = 0');
        $db->bind(':user_id', $userId);

        return $db->execute();
    }

    /**
     * Count unread
     */
    public static function countUnread($userId) {
        $db = new Database();

        $db->query('SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND `read` = 0');
        $db->bind(':user_id', $userId);

        $result = $db->fetch();
        return $result['count'];
    }

    /**
     * Create maintenance alert notification
     */
    public static function maintenanceAlert($userId, $vehicleId, $maintenanceType) {
        return self::create(
            $userId,
            'alert',
            'maintenance',
            'Maintenance Due',
            'Maintenance due for vehicle. Type: ' . $maintenanceType,
            APP_URL . '/maintenance'
        );
    }

    /**
     * Create document expiry notification
     */
    public static function documentExpiry($userId, $vehicleId, $documentType, $expiryDate) {
        return self::create(
            $userId,
            'warning',
            'document',
            'Document Expiring Soon',
            $documentType . ' expires on ' . date('d/m/Y', strtotime($expiryDate)),
            APP_URL . '/vehicles/view/' . $vehicleId
        );
    }

    /**
     * Create speed alert notification
     */
    public static function speedAlert($userId, $vehicleReg, $speed, $limit) {
        return self::create(
            $userId,
            'alert',
            'gps',
            'Speed Limit Exceeded',
            'Vehicle ' . $vehicleReg . ' exceeded speed limit: ' . round($speed) . ' km/h (limit: ' . $limit . ' km/h)',
            APP_URL . '/tracking/alerts'
        );
    }

    /**
     * Create geofence alert notification
     */
    public static function geofenceAlert($userId, $vehicleReg, $geofenceName, $alertType) {
        $action = $alertType === 'entry' ? 'entered' : 'exited';

        return self::create(
            $userId,
            'info',
            'gps',
            'Geofence Alert',
            'Vehicle ' . $vehicleReg . ' ' . $action . ' geofence: ' . $geofenceName,
            APP_URL . '/tracking/geofences'
        );
    }

    /**
     * Delete notification
     */
    public static function delete($notificationId) {
        $db = new Database();

        $db->query('DELETE FROM notifications WHERE id = :id');
        $db->bind(':id', $notificationId);

        return $db->execute();
    }
}
