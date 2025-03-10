<?php
/**
 * Settings Class
 * Handles system-wide settings and configuration
 */
class Settings {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get a system setting by key
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value or default
     */
    public function get($key, $default = null) {
        $stmt = $this->db->prepare("
            SELECT setting_value
            FROM system_settings
            WHERE setting_key = ?
        ");
        
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * Update a system setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return boolean Success
     */
    public function update($key, $value) {
        // Check if setting exists
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM system_settings
            WHERE setting_key = ?
        ");
        
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Update existing setting
            $stmt = $this->db->prepare("
                UPDATE system_settings
                SET setting_value = ?, updated_at = CURRENT_TIMESTAMP
                WHERE setting_key = ?
            ");
            
            return $stmt->execute([$value, $key]);
        } else {
            // Insert new setting
            $stmt = $this->db->prepare("
                INSERT INTO system_settings (setting_key, setting_value)
                VALUES (?, ?)
            ");
            
            return $stmt->execute([$key, $value]);
        }
    }
    
    /**
     * Get all system settings
     * 
     * @return array All settings
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT setting_key, setting_value, description
            FROM system_settings
            ORDER BY setting_key
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}