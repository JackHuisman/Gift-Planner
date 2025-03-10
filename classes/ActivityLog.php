<?php
// ActivityLog.php
class ActivityLog {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Log a user activity
     * 
     * @param int $user_id User ID
     * @param string $activity_type Type of activity
     * @param int|null $related_id ID of related record
     * @param array $activity_data Additional data about the activity
     * @return bool Success status
     */
    public function log($user_id, $activity_type, $related_id = null, $activity_data = []) {
        // Convert activity data to JSON
        $data_json = !empty($activity_data) ? json_encode($activity_data) : null;
        
        $stmt = $this->db->prepare("
            INSERT INTO activity_log (user_id, activity_type, related_id, activity_data)
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([$user_id, $activity_type, $related_id, $data_json]);
    }
    
    /**
     * Get recent activities for a user
     * 
     * @param int $user_id User ID
     * @param int $limit Maximum number of activities to return
     * @param int $offset Offset for pagination
     * @return array Recent activities
     */
    public function getRecentForUser($user_id, $limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   r.name as recipient_name, 
                   o.occasion_type,
                   gs.product_title as gift_title
            FROM activity_log a
            LEFT JOIN recipients r ON a.related_id = r.recipient_id AND a.activity_type IN ('recipient_added', 'recipient_updated')
            LEFT JOIN occasions o ON a.related_id = o.occasion_id AND a.activity_type IN ('occasion_added', 'occasion_updated')
            LEFT JOIN gift_suggestions gs ON a.related_id = gs.suggestion_id AND a.activity_type = 'gift_selected'
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$user_id, $limit, $offset]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Process activities to add readable details
        foreach ($activities as &$activity) {
            // Parse JSON data
            if (!empty($activity['activity_data'])) {
                $activity['data'] = json_decode($activity['activity_data'], true);
            } else {
                $activity['data'] = [];
            }
            
            // Set display text based on activity type
            switch ($activity['activity_type']) {
                case 'recipient_added':
                    $activity['text'] = "Added a new recipient: " . $activity['recipient_name'];
                    $activity['icon'] = 'fas fa-user-plus';
                    $activity['color'] = 'primary';
                    break;
                    
                case 'recipient_updated':
                    $activity['text'] = "Updated recipient: " . $activity['recipient_name'];
                    $activity['icon'] = 'fas fa-user-edit';
                    $activity['color'] = 'info';
                    break;
                    
                case 'recipient_deleted':
                    $activity['text'] = "Deleted recipient: " . ($activity['data']['recipient_name'] ?? 'Unknown');
                    $activity['icon'] = 'fas fa-user-times';
                    $activity['color'] = 'danger';
                    break;
                    
                case 'occasion_added':
                    $activity['text'] = "Added " . $activity['occasion_type'] . " for " . 
                                       ($activity['data']['recipient_name'] ?? 'someone');
                    $activity['icon'] = 'fas fa-calendar-plus';
                    $activity['color'] = 'success';
                    break;
                    
                case 'occasion_updated':
                    $activity['text'] = "Updated " . $activity['occasion_type'] . " for " . 
                                       ($activity['data']['recipient_name'] ?? 'someone');
                    $activity['icon'] = 'fas fa-calendar-alt';
                    $activity['color'] = 'info';
                    break;
                    
                case 'occasion_deleted':
                    $activity['text'] = "Deleted " . ($activity['data']['occasion_type'] ?? 'occasion') . 
                                       " for " . ($activity['data']['recipient_name'] ?? 'someone');
                    $activity['icon'] = 'fas fa-calendar-times';
                    $activity['color'] = 'danger';
                    break;
                    
                case 'gift_selected':
                    $activity['text'] = "Selected a gift for " . 
                                       ($activity['data']['recipient_name'] ?? 'someone') . "'s " . 
                                       ($activity['data']['occasion_type'] ?? 'occasion');
                    $activity['icon'] = 'fas fa-gift';
                    $activity['color'] = 'success';
                    $activity['details'] = $activity['gift_title'] ?? $activity['data']['gift_title'] ?? '';
                    break;
                    
                case 'suggestions_generated':
                    $activity['text'] = "Generated " . ($activity['data']['count'] ?? '') . 
                                       " gift suggestions for " . 
                                       ($activity['data']['recipient_name'] ?? 'someone') . "'s " . 
                                       ($activity['data']['occasion_type'] ?? 'occasion');
                    $activity['icon'] = 'fas fa-magic';
                    $activity['color'] = 'primary';
                    break;
                    
                case 'suggestions_refresh_viewed':
                    $activity['text'] = "Customized gift suggestions for " . 
                                       ($activity['data']['recipient_name'] ?? 'someone') . "'s " . 
                                       ($activity['data']['occasion_type'] ?? 'occasion');
                    $activity['icon'] = 'fas fa-sync-alt';
                    $activity['color'] = 'info';
                    break;
                    
                case 'account_updated':
                    $activity['text'] = "Updated account settings";
                    $activity['icon'] = 'fas fa-user-cog';
                    $activity['color'] = 'secondary';
                    break;
                    
                case 'password_changed':
                    $activity['text'] = "Changed account password";
                    $activity['icon'] = 'fas fa-key';
                    $activity['color'] = 'warning';
                    break;
                    
                default:
                    $activity['text'] = "Performed an action";
                    $activity['icon'] = 'fas fa-check';
                    $activity['color'] = 'secondary';
            }
        }
        
        return $activities;
    }
    
    /**
     * Count total activities for a user
     * 
     * @param int $user_id User ID
     * @return int Total count of activities
     */
    public function countForUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM activity_log
            WHERE user_id = ?
        ");
        
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Delete activities for a user
     * 
     * @param int $user_id User ID
     * @return bool Success status
     */
    public function deleteForUser($user_id) {
        $stmt = $this->db->prepare("
            DELETE FROM activity_log
            WHERE user_id = ?
        ");
        
        return $stmt->execute([$user_id]);
    }
}
?>