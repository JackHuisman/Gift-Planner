<?php
// Notifications.php (Updated)
class Notifications {
    private $db;
    private $amazonAPI;
    private $emailConfig;
    
    public function __construct($db, $amazonAPI, $emailConfig) {
        $this->db = $db;
        $this->amazonAPI = $amazonAPI;
        $this->emailConfig = $emailConfig;
    }
//    
//    public function processUpcomingOccasions() {
//        $occasion = new Occasion($this->db);
//        $giftSuggestion = new GiftSuggestion($this->db);
//        
//        // Get upcoming occasions needing reminders
//        $upcomingOccasions = $occasion->getUpcoming(14); // 14 days in advance
//        
//        foreach ($upcomingOccasions as $upcomingOccasion) {
//            // Generate keywords based on recipient info
//            $keywords = $this->generateKeywords(
//                $upcomingOccasion['recipient_name'],
//                $upcomingOccasion['gender'],
//                $upcomingOccasion['age'],
//                $upcomingOccasion['relationship'],
//                $upcomingOccasion['occasion_type']
//            );
//            
//            // Get gift suggestions from Amazon
//            $suggestions = $this->amazonAPI->searchGiftSuggestions(
//                $keywords,
//                $upcomingOccasion['price_min'],
//                $upcomingOccasion['price_max'],
//                5
//            );
//            
//            // Save suggestions to database
//            $giftSuggestion->saveMultiple($upcomingOccasion['occasion_id'], $suggestions);
//            
//            // Send email notification
//            $this->sendEmailNotification(
//                $upcomingOccasion['user_email'],
//                $upcomingOccasion['recipient_name'],
//                $upcomingOccasion['occasion_type'],
//                $upcomingOccasion['occasion_date'],
//                $suggestions
//            );
//            
//            // Mark reminder as sent
//            $occasion->markReminderSent($upcomingOccasion['occasion_id']);
//        }
//    }
//    

// Update the generateKeywords method in your Notifications class
/**
 * Generate keywords for gift suggestions based on recipient information
 * Updated to work with simplified age field
 * 
 * @param string $name Recipient name
 * @param string $gender Recipient gender
 * @param int $age Recipient age
 * @param string $relationship Relationship to recipient
 * @param string $occasionType Type of occasion
 * @return string Keywords for gift search
 */
private function generateKeywords($name, $gender, $age, $relationship, $occasionType) {
    $keywords = "gift for ";
    
    if ($gender) {
        $keywords .= strtolower($gender) . " ";
    }
    
    if ($age) {
        $keywords .= $age . " year old ";
        
        // Add age-specific terms
        if ($age < 3) {
            $keywords .= "baby toddler ";
        } else if ($age < 13) {
            $keywords .= "kid child ";
        } else if ($age < 20) {
            $keywords .= "teen teenager ";
        } else if ($age > 65) {
            $keywords .= "senior ";
        }
    }
    
    if ($relationship) {
        $keywords .= strtolower($relationship) . " ";
    }
    
    $keywords .= "for " . strtolower($occasionType);
    
    return $keywords;
}
    
    // Update this method in your Notifications class
private function sendEmailNotification($email, $recipientName, $occasionType, $occasionDate, $suggestions, $affiliateId = null) {
    $subject = "Gift Ideas for {$recipientName}'s {$occasionType}";
    
    // Build email body
    $body = "<h1>Gift Ideas for {$recipientName}'s {$occasionType}</h1>";
    $body .= "<p>Here are some gift suggestions for {$recipientName}'s upcoming {$occasionType} on " . date('F j, Y', strtotime($occasionDate)) . ":</p>";
    
    // Use the global Amazon affiliate ID from config
    global $amazonConfig;
    $affiliateId = $amazonConfig['partner_tag'];
    
    foreach ($suggestions as $suggestion) {
        // Add affiliate tag to URL
        $url = $suggestion['amazon_url'];
        $url = $this->addAffiliateTag($url, $affiliateId);
        
        $body .= "<div style='margin-bottom: 20px;'>";
        $body .= "<h2><a href='{$url}'>{$suggestion['product_title']}</a></h2>";
        $body .= "<p><strong>Price:</strong> $" . number_format($suggestion['price'] / 100, 2) . "</p>";
        
        if (!empty($suggestion['image_url'])) {
            $body .= "<img src='{$suggestion['image_url']}' alt='{$suggestion['product_title']}' style='max-width: 200px;'><br>";
        }
        
        if (!empty($suggestion['product_description'])) {
            $body .= "<p>" . nl2br($suggestion['product_description']) . "</p>";
        }
        
        $body .= "<p><a href='{$url}'>View on Amazon</a></p>";
        $body .= "</div>";
        $body .= "<hr>";
    }
    
    $body .= "<p>Log in to your account to see more details or mark gifts as selected.</p>";
    
    // Send the email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: {$this->emailConfig['from_email']}" . "\r\n";
    
    mail($email, $subject, $body, $headers);
}

/**
 * Process upcoming occasions and send notifications
 */
public function processUpcomingOccasions() {
    $occasion = new Occasion($this->db);
    $giftSuggestion = new GiftSuggestion($this->db);
    
    // Get upcoming occasions needing reminders
    $upcomingOccasions = $occasion->getUpcoming(14); // 14 days in advance
    
    foreach ($upcomingOccasions as $upcomingOccasion) {
        // Check if we already have gift suggestions for this occasion
        $existingSuggestions = $giftSuggestion->getByOccasionId($upcomingOccasion['occasion_id']);
        
        // If no suggestions exist, generate them using our predefined system
        if (empty($existingSuggestions)) {
            $giftSuggestion->generateSuggestions($upcomingOccasion['occasion_id'], 5);
            
            // Refresh to get the newly generated suggestions
            $suggestions = $giftSuggestion->getByOccasionId($upcomingOccasion['occasion_id']);
        } else {
            $suggestions = $existingSuggestions;
        }
        
        // Send email notification if we have suggestions
        if (!empty($suggestions)) {
            $this->sendEmailNotification(
                $upcomingOccasion['user_email'],
                $upcomingOccasion['recipient_name'],
                $upcomingOccasion['occasion_type'],
                $upcomingOccasion['occasion_date'],
                $suggestions
            );
            
            // Mark reminder as sent
            $occasion->markReminderSent($upcomingOccasion['occasion_id']);
        }
    }
}

}