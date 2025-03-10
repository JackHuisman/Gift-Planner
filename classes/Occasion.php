<?php
// Occasion.php
class Occasion {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new occasion with special date handling
     * 
     * @param int $recipient_id Recipient ID
     * @param string $occasion_type Type of occasion
     * @param string $occasion_date Date of occasion
     * @param string $specific_date Specific date for birthdays and anniversaries
     * @param float $price_min Minimum price
     * @param float $price_max Maximum price
     * @param bool $is_annual Whether the occasion repeats annually
     * @return bool Success status
     */
    public function create($recipient_id, $occasion_type, $occasion_date, $specific_date = null, $price_min = 0, $price_max = 100, $is_annual = true) {
        // For special occasion types, set the specific date
        if (in_array($occasion_type, ['Birthday', 'Anniversary']) && empty($specific_date)) {
            // For birthdays and anniversaries, the specific date is important
            // but the occasion date is used for the current year's event
            $specific_date = $occasion_date;
        }
        
        // For fixed holidays, set the occasion date automatically
        if (in_array($occasion_type, ['Christmas', 'Valentine\'s Day', 'Halloween', 'New Year']) && empty($occasion_date)) {
            $occasion_date = getDefaultOccasionDate($occasion_type);
        }
        
        // For variable holidays, set the occasion date based on the current year
        if (in_array($occasion_type, ['Mother\'s Day', 'Father\'s Day', 'Easter', 'Thanksgiving']) && empty($occasion_date)) {
            $occasion_date = getDefaultOccasionDate($occasion_type);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO occasions (recipient_id, occasion_type, occasion_date, specific_date, price_min, price_max, is_annual)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$recipient_id, $occasion_type, $occasion_date, $specific_date, $price_min, $price_max, $is_annual]);
    }
    
    /**
     * Get an occasion by ID
     * 
     * @param int $occasion_id Occasion ID
     * @return array|bool Occasion data or false if not found
     */
    public function getById($occasion_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM occasions
            WHERE occasion_id = ?
        ");
        
        $stmt->execute([$occasion_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all occasions for a recipient
     * 
     * @param int $recipient_id Recipient ID
     * @return array Occasions data
     */
    public function getByRecipientId($recipient_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM occasions
            WHERE recipient_id = ? AND is_active = TRUE
            ORDER BY occasion_date
        ");
        
        $stmt->execute([$recipient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get upcoming occasions for a specific user
     * 
     * @param int $user_id User ID
     * @param int $days Number of days to look ahead
     * @return array Upcoming occasions
     */
    public function getUpcomingForUser($user_id, $days = 30) {
        // First, update all annual occasion dates
        $this->updateAllAnnualDates();
        
        $stmt = $this->db->prepare("
            SELECT o.*, r.name as recipient_name, r.gender, r.age, r.relationship
            FROM occasions o
            JOIN recipients r ON o.recipient_id = r.recipient_id
            WHERE r.user_id = ? 
            AND o.is_active = TRUE 
            AND r.is_active = TRUE
            AND o.occasion_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY o.occasion_date
        ");
        
        $stmt->execute([$user_id, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
 * Update an existing occasion
 * 
 * @param int $occasion_id Occasion ID
 * @param string $occasion_type Type of occasion
 * @param string $occasion_date Date of occurrence
 * @param string $specific_date Specific date for birthdays, anniversaries, etc.
 * @param float $price_min Minimum price
 * @param float $price_max Maximum price
 * @param bool $is_annual Whether the occasion repeats annually
 * @param string $notes Notes about gift preferences
 * @return bool Success status
 */
public function update($occasion_id, $occasion_type, $occasion_date, $specific_date = null, 
                      $price_min = 0, $price_max = 100, $is_annual = false, $notes = '') {
    
    $stmt = $this->db->prepare("
        UPDATE occasions
        SET occasion_type = ?, 
            occasion_date = ?, 
            specific_date = ?,
            price_min = ?, 
            price_max = ?, 
            is_annual = ?,
            notes = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE occasion_id = ?
    ");
    
    return $stmt->execute([
        $occasion_type, 
        $occasion_date, 
        $specific_date,
        $price_min, 
        $price_max, 
        $is_annual ? 1 : 0, 
        $notes, 
        $occasion_id
    ]);
}
    
    /**
     * Count occasions by user ID
     * 
     * @param int $userId User ID
     * @return int Count of occasions
     */
    public function countByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(o.occasion_id) as count
            FROM occasions o
            JOIN recipients r ON o.recipient_id = r.recipient_id
            WHERE r.user_id = ? AND o.is_active = TRUE
        ");
        
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Deactivate an occasion
     * 
     * @param int $occasion_id Occasion ID
     * @return bool Success status
     */
    public function deactivate($occasion_id) {
        $stmt = $this->db->prepare("
            UPDATE occasions
            SET is_active = FALSE, updated_at = CURRENT_TIMESTAMP
            WHERE occasion_id = ?
        ");
        
        return $stmt->execute([$occasion_id]);
    }
    
    /**
     * Activate an occasion
     * 
     * @param int $occasion_id Occasion ID
     * @return bool Success status
     */
    public function activate($occasion_id) {
        $stmt = $this->db->prepare("
            UPDATE occasions
            SET is_active = TRUE, updated_at = CURRENT_TIMESTAMP
            WHERE occasion_id = ?
        ");
        
        return $stmt->execute([$occasion_id]);
    }
    
    /**
     * Permanently delete an occasion
     * 
     * @param int $occasion_id Occasion ID
     * @return bool Success status
     */
    public function delete($occasion_id) {
        // First, delete all gift suggestions for this occasion
        $stmt = $this->db->prepare("
            DELETE FROM gift_suggestions
            WHERE occasion_id = ?
        ");
        $stmt->execute([$occasion_id]);
        
        // Then delete the occasion
        $stmt = $this->db->prepare("
            DELETE FROM occasions
            WHERE occasion_id = ?
        ");
        
        return $stmt->execute([$occasion_id]);
    }
    
    /**
     * Mark reminder as sent
     * 
     * @param int $occasion_id Occasion ID
     * @return bool Success status
     */
    public function markReminderSent($occasion_id) {
        $stmt = $this->db->prepare("
            UPDATE occasions
            SET reminder_sent = TRUE
            WHERE occasion_id = ?
        ");
        
        return $stmt->execute([$occasion_id]);
    }
    
    /**
     * Update occasion date for annual events
     * Used to update the occasion_date field for the current year while preserving specific_date
     * 
     * @param int $occasion_id Occasion ID
     * @return bool Success status
     */
    public function updateAnnualDate($occasion_id) {
        // Get occasion details
        $stmt = $this->db->prepare("
            SELECT * FROM occasions
            WHERE occasion_id = ? AND is_annual = TRUE
        ");
        
        $stmt->execute([$occasion_id]);
        $occasion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$occasion) {
            return false;
        }
        
        $currentYear = date('Y');
        $newDate = null;
        
        // Handle different types of occasions
        switch ($occasion['occasion_type']) {
            case 'Birthday':
            case 'Anniversary':
                // Use the month and day from specific_date, but current year
                if (!empty($occasion['specific_date'])) {
                    $month = date('m', strtotime($occasion['specific_date']));
                    $day = date('d', strtotime($occasion['specific_date']));
                    $newDate = "$currentYear-$month-$day";
                    
                    // If this date has already passed this year, use next year
                    if (strtotime($newDate) < time()) {
                        $newDate = ($currentYear + 1) . "-$month-$day";
                    }
                }
                break;
                
            case 'Christmas':
            case 'Valentine\'s Day':
            case 'Halloween':
            case 'New Year':
            case 'Mother\'s Day':
            case 'Father\'s Day':
            case 'Easter':
            case 'Thanksgiving':
                // Use the holiday calculation function
                $newDate = getDefaultOccasionDate($occasion['occasion_type'], $currentYear);
                
                // If this date has already passed this year, use next year
                if (strtotime($newDate) < time()) {
                    $newDate = getDefaultOccasionDate($occasion['occasion_type'], $currentYear + 1);
                }
                break;
                
            default:
                // For custom events, increment the year but keep month and day
                if (!empty($occasion['occasion_date'])) {
                    $month = date('m', strtotime($occasion['occasion_date']));
                    $day = date('d', strtotime($occasion['occasion_date']));
                    $newDate = "$currentYear-$month-$day";
                    
                    // If this date has already passed this year, use next year
                    if (strtotime($newDate) < time()) {
                        $newDate = ($currentYear + 1) . "-$month-$day";
                    }
                }
                break;
        }
        
        if ($newDate) {
            $updateStmt = $this->db->prepare("
                UPDATE occasions
                SET occasion_date = ?
                WHERE occasion_id = ?
            ");
            
            return $updateStmt->execute([$newDate, $occasion_id]);
        }
        
        return false;
    }
    
    /**
     * Get upcoming occasions that need reminders
     * Also updates dates for annual occasions if needed
     * 
     * @param int $days Number of days in advance to check
     * @return array Upcoming occasions
     */
    public function getUpcoming($days = 14) {
        // First, update all annual occasion dates
        $this->updateAllAnnualDates();
        
        $stmt = $this->db->prepare("
            SELECT o.*, r.name as recipient_name, r.gender, r.age, r.relationship, u.email as user_email, u.amazon_affiliate_id
            FROM occasions o
            JOIN recipients r ON o.recipient_id = r.recipient_id
            JOIN users u ON r.user_id = u.user_id
            WHERE o.is_active = TRUE 
            AND r.is_active = TRUE
            AND o.reminder_sent = FALSE
            AND o.occasion_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY o.occasion_date
        ");
        
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Update all annual occasion dates to ensure they're current
     * 
     * @return int Number of updated occasions
     */
    private function updateAllAnnualDates() {
        $currentYear = date('Y');
        $updatedCount = 0;
        
        // Get all active annual occasions
        $stmt = $this->db->prepare("
            SELECT occasion_id, occasion_type, occasion_date, specific_date
            FROM occasions
            WHERE is_annual = TRUE AND is_active = TRUE
        ");
        
        $stmt->execute();
        $occasions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($occasions as $occasion) {
            // Skip if the occasion date is already in the current or future
            if (!empty($occasion['occasion_date']) && strtotime($occasion['occasion_date']) >= strtotime(date('Y-m-d'))) {
                continue;
            }
            
            if ($this->updateAnnualDate($occasion['occasion_id'])) {
                $updatedCount++;
            }
        }
        
        return $updatedCount;
    }
}