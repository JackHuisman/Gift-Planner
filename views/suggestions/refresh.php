<?php
/**
 * Refresh Gift Suggestions Page
 * Shows form to customize parameters for refreshing gift suggestions
 */

// Add a guard to prevent double inclusion
if (defined('REFRESH_PAGE_INCLUDED')) {
    return; // Exit if this file has already been included
}
define('REFRESH_PAGE_INCLUDED', true);

$pageTitle = 'Refresh Gift Suggestions';

// Get occasion and recipient information
$occasion = new Occasion($db);
$occasionData = $occasion->getById($occasionId);

$recipient = new Recipient($db);
$recipientData = $recipient->getById($occasionData['recipient_id']);

// Capture timestamps but don't display them
$occasionCreatedAt = $occasionData['created_at'] ?? null;
$occasionUpdatedAt = $occasionData['updated_at'] ?? null;

// Generate default keywords based on recipient information
$defaultKeywords = 'gift for ';

// Add gender if available
if (!empty($recipientData['gender'])) {
    $defaultKeywords .= strtolower($recipientData['gender']) . ' ';
}

// Add age in a natural way
if (!empty($recipientData['age'])) {
    $age = (int)$recipientData['age'];
    
    if ($age <= 2) {
        $defaultKeywords .= 'baby 0-2 years ';
    } else if ($age <= 4) {
        $defaultKeywords .= 'toddler 3-4 years ';
    } else if ($age <= 12) {
        $defaultKeywords .= 'child 5-12 years ';
    } else if ($age <= 17) {
        $defaultKeywords .= 'teenager 13-17 years ';
    } else if ($age <= 24) {
        $defaultKeywords .= 'young adult 18-24 years ';
    } else if ($age <= 39) {
        $defaultKeywords .= 'adult 25-39 years ';
    } else if ($age <= 59) {
        $defaultKeywords .= 'middle-aged 40-59 years ';
    } else {
        $defaultKeywords .= 'senior 60+ years ';
    }
}

// Add relationship with proper wording
if (!empty($recipientData['relationship'])) {
    // Handle different relationship types naturally
    switch(strtolower($recipientData['relationship'])) {
        case 'family':
            $defaultKeywords .= 'family member ';
            break;
        case 'friend':
            $defaultKeywords .= 'friend ';
            break;
        case 'colleague':
            $defaultKeywords .= 'colleague ';
            break;
        case 'partner':
            $defaultKeywords .= 'partner ';
            break;
        case 'spouse':
            $defaultKeywords .= 'spouse ';
            break;
        case 'child':
            // Don't add "child" twice if age category already indicates child
            if (strpos($defaultKeywords, 'child') === false && 
                strpos($defaultKeywords, 'baby') === false && 
                strpos($defaultKeywords, 'toddler') === false) {
                $defaultKeywords .= 'child ';
            }
            break;
        case 'parent':
            $defaultKeywords .= 'parent ';
            break;
        case 'sibling':
            $defaultKeywords .= 'sibling ';
            break;
        default:
            $defaultKeywords .= strtolower($recipientData['relationship']) . ' ';
    }
}

// Add occasion
$defaultKeywords .= 'for ' . strtolower($occasionData['occasion_type']);
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>"><?= h($recipientData['name']) ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=occasions&action=view&id=<?= $occasionData['occasion_id'] ?>"><?= h($occasionData['occasion_type']) ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=suggestions&occasion_id=<?= $occasionData['occasion_id'] ?>">Gift Suggestions</a></li>
        <li class="breadcrumb-item active" aria-current="page">Refresh Suggestions</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Customize Gift Suggestions Parameters</h5>
            </div>
            <div class="card-body">
                <p class="mb-4">
                    Customize the parameters below to get new gift suggestions for <?= h($recipientData['name']) ?>'s <?= h($occasionData['occasion_type']) ?>.
                </p>
                
                <form action="index.php?page=suggestions&action=generate" method="post">
                    <input type="hidden" name="occasion_id" value="<?= $occasionId ?>">
                    
                    <div class="mb-3">
                        <label for="keywords" class="form-label">Search Keywords</label>
                        <input type="text" class="form-control" id="keywords" name="keywords" 
                               value="<?= h($defaultKeywords) ?>">
                        <div class="form-text">Customize the search keywords to find more relevant gifts.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price_min" class="form-label">Minimum Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price_min" name="price_min" min="0" step="0.01" 
                                       value="<?= $occasionData['price_min'] ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="price_max" class="form-label">Maximum Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price_max" name="price_max" min="0" step="0.01" 
                                       value="<?= $occasionData['price_max'] ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="count" class="form-label">Number of Suggestions</label>
                        <select class="form-select" id="count" name="count">
                            <option value="5" selected>5 suggestions</option>
                            <option value="10">10 suggestions</option>
                            <option value="15">15 suggestions</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=suggestions&occasion_id=<?= $occasionId ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-1"></i> Generate New Suggestions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden div for timestamps data -->
<div id="occasion-timestamps" data-created="<?= h($occasionCreatedAt) ?>" data-updated="<?= h($occasionUpdatedAt) ?>" style="display: none;"></div>