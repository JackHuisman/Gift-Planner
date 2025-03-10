<?php
/**
 * Activity page handler
 * Manages all activity-related actions
 */

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Set page title
$pageTitle = 'All Activity';

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : 'view-all';

if ($action == 'view-all') {
    // Get filter parameter if present
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    
    // Get all occasions for this user
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare("
        SELECT o.*, r.name as recipient_name, r.gender, r.age, r.relationship
        FROM occasions o
        JOIN recipients r ON o.recipient_id = r.recipient_id
        WHERE r.user_id = ? AND o.is_active = TRUE AND r.is_active = TRUE
        ORDER BY o.occasion_date
    ");
    $stmt->execute([$user_id]);
    $allOccasions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process occasions for display
    $occasions = [];
    $today = new DateTime();

    foreach ($allOccasions as $occ) {
        $occasionDate = new DateTime($occ['occasion_date']);
        $nextOccurrence = clone $occasionDate;
        
        // If it's an annual occasion, adjust the year to current or next year
        if ($occ['is_annual']) {
            $nextOccurrence->setDate(
                $today->format('Y'), 
                $occasionDate->format('m'), 
                $occasionDate->format('d')
            );
            
            // If this date has already passed this year, move to next year
            if ($nextOccurrence < $today) {
                $nextOccurrence->modify('+1 year');
            }
        }
        
        // Calculate days until
        $daysUntil = $today->diff($nextOccurrence)->days;
        $in_past = $nextOccurrence < $today;
        
        // Get gift suggestions if any
        $giftSuggestion = new GiftSuggestion($db);
        $suggestions = $giftSuggestion->getByOccasionId($occ['occasion_id']);
        $selectedSuggestion = null;
        
        foreach ($suggestions as $sugg) {
            if ($sugg['is_selected']) {
                $selectedSuggestion = $sugg;
                break;
            }
        }
        
        $occasions[] = [
            'recipient_name' => $occ['recipient_name'],
            'recipient_id' => $occ['recipient_id'],
            'occasion_id' => $occ['occasion_id'],
            'occasion_type' => $occ['occasion_type'],
            'original_date' => $occ['occasion_date'],
            'next_date' => $nextOccurrence->format('Y-m-d'),
            'days_until' => $daysUntil,
            'in_past' => $in_past,
            'price_min' => $occ['price_min'],
            'price_max' => $occ['price_max'],
            'has_suggestions' => count($suggestions) > 0,
            'suggestion_count' => count($suggestions),
            'selected_gift' => $selectedSuggestion,
            'is_annual' => $occ['is_annual'],
            'status_class' => getOccasionStatusClass($daysUntil)
        ];
    }

    // Filter the occasions based on the selected filter
    $filteredOccasions = [];

    if ($filter == 'upcoming') {
        foreach ($occasions as $occ) {
            if (!$occ['in_past']) {
                $filteredOccasions[] = $occ;
            }
        }
    } elseif ($filter == 'past') {
        foreach ($occasions as $occ) {
            if ($occ['in_past']) {
                $filteredOccasions[] = $occ;
            }
        }
    } elseif ($filter == 'selected') {
        foreach ($occasions as $occ) {
            if ($occ['selected_gift']) {
                $filteredOccasions[] = $occ;
            }
        }
    } elseif ($filter == 'unselected') {
        foreach ($occasions as $occ) {
            if (!$occ['selected_gift'] && $occ['has_suggestions']) {
                $filteredOccasions[] = $occ;
            }
        }
    } elseif ($filter == 'no-suggestions') {
        foreach ($occasions as $occ) {
            if (!$occ['has_suggestions']) {
                $filteredOccasions[] = $occ;
            }
        }
    } else {
        $filteredOccasions = $occasions;
    }
    
    // Sort by days until (ascending)
    usort($filteredOccasions, function($a, $b) {
        // Put past occasions at the end
        if ($a['in_past'] && !$b['in_past']) {
            return 1;
        } elseif (!$a['in_past'] && $b['in_past']) {
            return -1;
        }
        
        // Sort by days until
        return $a['days_until'] - $b['days_until'];
    });
    
    // Include the view template
//    include 'views/activity/view-all.php';
include(__DIR__ . '/activity/view-all.php');
} else {
    // Handle other activity actions if needed in the future
    // Default to view-all if action not recognized
    header('Location: index.php?page=activity&action=view-all');
    exit;
}