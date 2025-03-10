<?php
// GiftSuggestion.php
class GiftSuggestion {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Count the total number of gift suggestions for a user
     * 
     * @param int $userId User ID
     * @return int Number of suggestions
     */
    public function countForUser($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(gs.suggestion_id) as count
            FROM gift_suggestions gs
            JOIN occasions o ON gs.occasion_id = o.occasion_id
            JOIN recipients r ON o.recipient_id = r.recipient_id
            WHERE r.user_id = ?
        ");
        
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Count the number of selected gift suggestions for a user
     * 
     * @param int $userId User ID
     * @return int Number of selected suggestions
     */
    public function countSelectedByUserId($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(gs.suggestion_id) as count
            FROM gift_suggestions gs
            JOIN occasions o ON gs.occasion_id = o.occasion_id
            JOIN recipients r ON o.recipient_id = r.recipient_id
            WHERE r.user_id = ? AND gs.is_selected = TRUE
        ");
        
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['count'] : 0;
    }
    
    /**
     * Get a gift suggestion by ID
     * 
     * @param int $suggestionId Suggestion ID
     * @return array|bool Suggestion data or false if not found
     */
    public function getById($suggestionId) {
        $stmt = $this->db->prepare("
            SELECT * FROM gift_suggestions
            WHERE suggestion_id = ?
        ");
        
        $stmt->execute([$suggestionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
//    public function saveMultiple($occasion_id, $suggestions) {
//        foreach ($suggestions as $suggestion) {
//            $stmt = $this->db->prepare("
//                INSERT INTO gift_suggestions 
//                (occasion_id, product_title, product_description, amazon_url, amazon_asin, price, image_url)
//                VALUES (?, ?, ?, ?, ?, ?, ?)
//            ");
//            
//            $stmt->execute([
//                $occasion_id,
//                $suggestion['product_title'],
//                $suggestion['product_description'],
//                $suggestion['amazon_url'],
//                $suggestion['amazon_asin'],
//                $suggestion['price'],
//                $suggestion['image_url']
//            ]);
//        }
//        
//        return true;
//    }
    
    public function getByOccasionId($occasion_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM gift_suggestions
            WHERE occasion_id = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$occasion_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function markSelected($suggestion_id) {
        $stmt = $this->db->prepare("
            UPDATE gift_suggestions
            SET is_selected = TRUE
            WHERE suggestion_id = ?
        ");
        
        return $stmt->execute([$suggestion_id]);
    }
    
    /**
     * Get recently selected gifts for a user
     * 
     * @param int $userId User ID
     * @param int $limit Maximum number of records to return
     * @return array Selected gift suggestions
     */
    public function getRecentlySelectedByUserId($userId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT gs.*, o.occasion_type, r.name as recipient_name
            FROM gift_suggestions gs
            JOIN occasions o ON gs.occasion_id = o.occasion_id
            JOIN recipients r ON o.recipient_id = r.recipient_id
            WHERE r.user_id = ? AND gs.is_selected = TRUE
            ORDER BY gs.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
 * Get gift suggestions without using PA API
 * 
 * @param string $category Gift category (e.g., "birthday", "men", "women")
 * @param float $minPrice Minimum price
 * @param float $maxPrice Maximum price
 * @return array Gift suggestions
 */
public function getBasicSuggestions($category, $minPrice, $maxPrice) {
    // Query your pre-populated suggestions table
    $stmt = $this->db->prepare("
        SELECT * FROM predefined_gift_suggestions
        WHERE category LIKE ? 
        AND price BETWEEN ? AND ?
        ORDER BY popularity DESC
        LIMIT 5
    ");
    
    $stmt->execute(["%$category%", $minPrice, $maxPrice]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Save multiple gift suggestions for an occasion
 * 
 * @param int $occasion_id Occasion ID
 * @param array $suggestions Array of suggestion data
 * @return bool Success status
 */
public function saveMultiple($occasion_id, $suggestions) {
    foreach ($suggestions as $suggestion) {
        $stmt = $this->db->prepare("
            INSERT INTO gift_suggestions 
            (occasion_id, product_title, product_description, amazon_url, amazon_asin, price, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Generate Amazon URL if not provided
        $amazonUrl = $suggestion['amazon_url'] ?? "https://www.amazon.com/dp/{$suggestion['amazon_asin']}?tag={$GLOBALS['amazonConfig']['partner_tag']}";
        
        $stmt->execute([
            $occasion_id,
            $suggestion['product_title'],
            $suggestion['product_description'] ?? '',
            $amazonUrl,
            $suggestion['amazon_asin'],
            $suggestion['price'],
            $suggestion['image_url'] ?? ''
        ]);
    }
    
    return true;
}

/**
 * Generate gift suggestions for an occasion using predefined suggestions
 * Updated to work with simplified age field
 * 
 * @param int $occasion_id Occasion ID
 * @param int $count Number of suggestions to generate
 * @return bool Success status
 */
public function generateSuggestions($occasion_id, $count = 5) {
    // Get occasion details
    $occasion = new Occasion($this->db);
    $occasionData = $occasion->getById($occasion_id);
    
    if (!$occasionData) {
        return false;
    }
    
    // Get recipient details
    $recipient = new Recipient($this->db);
    $recipientData = $recipient->getById($occasionData['recipient_id']);
    
    if (!$recipientData) {
        return false;
    }
    
    // Prepare recipient data for suggestion matching
    $recipientInfo = [
        'age' => $recipientData['age'],
        'gender' => $recipientData['gender'],
        'relationship' => $recipientData['relationship']
    ];
    
    // Get predefined suggestions
    $predefinedSuggestion = new PredefinedGiftSuggestion($this->db);
    $suggestions = $predefinedSuggestion->getSuggestions(
        $occasionData['occasion_type'],
        $recipientInfo,
        $occasionData['price_min'],
        $occasionData['price_max'],
        $count
    );
    
    // If we have suggestions, save them
    if (!empty($suggestions)) {
        $formattedSuggestions = [];
        
        foreach ($suggestions as $suggestion) {
            $formattedSuggestions[] = [
                'product_title' => $suggestion['product_title'],
                'product_description' => $suggestion['product_description'],
                'amazon_asin' => $suggestion['amazon_asin'],
                'amazon_url' => $predefinedSuggestion->generateAffiliateLink($suggestion['amazon_asin']),
                'price' => $suggestion['price'], // Don't convert to cents, handle in display
                'image_url' => $suggestion['image_url']
            ];
        }
        
        return $this->saveMultiple($occasion_id, $formattedSuggestions);
    }
    
    return false;
}

/**
 * Delete all gift suggestions for an occasion
 * 
 * @param int $occasion_id Occasion ID
 * @return bool Success status
 */
public function deleteForOccasion($occasion_id) {
    $stmt = $this->db->prepare("
        DELETE FROM gift_suggestions
        WHERE occasion_id = ?
    ");
    
    return $stmt->execute([$occasion_id]);
}

    // Other gift suggestion related methods
}
?>