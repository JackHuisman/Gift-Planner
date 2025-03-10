<?php
// PredefinedGiftSuggestion.php
class PredefinedGiftSuggestion {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Add a new predefined gift suggestion
     * 
     * @param array $data Gift suggestion data
     * @return int|bool New suggestion ID or false on failure
     */
    public function add($data) {
        $stmt = $this->db->prepare("
            INSERT INTO predefined_gift_suggestions (
                product_title, 
                product_description, 
                amazon_asin, 
                price, 
                image_url, 
                category, 
                occasion_type, 
                age_min, 
                age_max, 
                gender, 
                relationship,
                is_featured
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $data['product_title'],
            $data['product_description'] ?? null,
            $data['amazon_asin'],
            $data['price'],
            $data['image_url'] ?? null,
            $data['category'],
            $data['occasion_type'],
            $data['age_min'] ?? null,
            $data['age_max'] ?? null,
            $data['gender'] ?? null,
            $data['relationship'] ?? null,
            $data['is_featured'] ?? false
        ]);
        
        return $success ? $this->db->lastInsertId() : false;
    }
    
    /**
 * Get suggestions based on occasion, recipient details, and price range
 * Updated to work with simplified age field
 * 
 * @param string $occasionType Type of occasion
 * @param array $recipient Recipient details (age, gender, relationship)
 * @param float $minPrice Minimum price
 * @param float $maxPrice Maximum price
 * @param int $limit Maximum number of suggestions to return
 * @return array Matched gift suggestions
 */
public function getSuggestions($occasionType, $recipient = [], $minPrice = 0, $maxPrice = 1000, $limit = 5) {
    // Start with a very lenient query to ensure we get results
    $sql = "
        SELECT * FROM predefined_gift_suggestions
        WHERE price BETWEEN ? AND ?
    ";
    
    $params = [
        $minPrice,
        $maxPrice
    ];
    
    // Try to match occasion if provided, but use LIKE for flexible matching
    if (!empty($occasionType)) {
        $sql .= " AND (occasion_type LIKE ? OR occasion_type = 'Any')";
        $params[] = "%$occasionType%";
    }
    
    // Add gender filter if provided, but keep it flexible
    if (!empty($recipient['gender'])) {
        $sql .= " AND (gender IS NULL OR gender = ? OR gender = 'Any')";
        $params[] = $recipient['gender'];
    }
    
    // Age filter if provided
    if (!empty($recipient['age']) && is_numeric($recipient['age'])) {
        $age = (int)$recipient['age'];
        $sql .= " AND (age_min IS NULL OR age_max IS NULL OR (age_min <= ? AND age_max >= ?))";
        $params[] = $age;
        $params[] = $age;
    }
    
    // Relationship filter if provided, but keep it flexible
    if (!empty($recipient['relationship'])) {
        $sql .= " AND (relationship IS NULL OR relationship LIKE ? OR relationship = 'Any')";
        $params[] = "%{$recipient['relationship']}%";
    }
    
    // Order by popularity and limit results
    $sql .= " ORDER BY RAND() LIMIT ?";
    $params[] = $limit;
    
    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add debug logging if available
        if (function_exists('error_log')) {
            error_log("PredefinedGiftSuggestion::getSuggestions found " . count($suggestions) . " suggestions");
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
        }
        
        // Return whatever we found
        return $suggestions;
    } catch (PDOException $e) {
        // Log the error if available
        if (function_exists('error_log')) {
            error_log("Error in PredefinedGiftSuggestion::getSuggestions: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . json_encode($params));
        }
        
        // Return empty array on error
        return [];
    }
}

    /**
     * Update suggestion popularity when selected
     * 
     * @param int $suggestionId Suggestion ID
     * @return bool Success status
     */
    public function incrementPopularity($suggestionId) {
        $stmt = $this->db->prepare("
            UPDATE predefined_gift_suggestions
            SET popularity = popularity + 1
            WHERE suggestion_id = ?
        ");
        
        return $stmt->execute([$suggestionId]);
    }
    
    /**
     * Get featured gift suggestions for display on dashboard
     * 
     * @param int $limit Maximum number of suggestions
     * @return array Featured gift suggestions
     */
    public function getFeatured($limit = 6) {
        $stmt = $this->db->prepare("
            SELECT * FROM predefined_gift_suggestions
            WHERE is_featured = TRUE
            ORDER BY popularity DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Search gift suggestions by keyword
     * 
     * @param string $keyword Search keyword
     * @param float $minPrice Minimum price
     * @param float $maxPrice Maximum price
     * @param int $limit Maximum number of results
     * @return array Matching gift suggestions
     */
    public function search($keyword, $minPrice = 0, $maxPrice = 1000, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM predefined_gift_suggestions
            WHERE (
                product_title LIKE ? OR
                product_description LIKE ? OR
                category LIKE ? OR
                occasion_type LIKE ? OR
                relationship LIKE ?
            )
            AND price BETWEEN ? AND ?
            ORDER BY popularity DESC
            LIMIT ?
        ");
        
        $searchTerm = "%$keyword%";
        $stmt->execute([
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $searchTerm,
            $minPrice,
            $maxPrice,
            $limit
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate Amazon affiliate link for a product
     * 
     * @param string $asin Amazon ASIN
     * @return string Formatted affiliate link
     */
    public function generateAffiliateLink($asin) {
        global $amazonConfig;
        
        $affiliateId = $amazonConfig['partner_tag'];
        return "https://www.amazon.com/dp/{$asin}?tag={$affiliateId}";
    }
}
?>