<?php
/**
 * View Single Gift Suggestion
 * Displays detailed information about a specific gift suggestion
 */

$pageTitle = 'Gift Details';

// Get suggestion information
$suggestionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$giftSuggestion = new GiftSuggestion($db);
$suggestionData = $giftSuggestion->getById($suggestionId);

// Get occasion and recipient information
$occasion = new Occasion($db);
$occasionData = $occasion->getById($suggestionData['occasion_id']);

$recipient = new Recipient($db);
$recipientData = $recipient->getById($occasionData['recipient_id']);

// Verify ownership
if (!$suggestionData || !$occasionData || !$recipientData || $recipientData['user_id'] !== $_SESSION['user_id']) {
    $errorTitle = 'Access Denied';
    $errorMessage = 'You do not have permission to view this gift suggestion.';
    include VIEWS_PATH . '/error.php';
    exit;
}

// Capture creation and update timestamps (but don't display them)
$createdAt = $suggestionData['created_at'] ?? null;
$updatedAt = $suggestionData['updated_at'] ?? null;
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>"><?= h($recipientData['name']) ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=occasions&action=view&id=<?= $occasionData['occasion_id'] ?>"><?= h($occasionData['occasion_type']) ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=suggestions&occasion_id=<?= $occasionData['occasion_id'] ?>">Gift Suggestions</a></li>
        <li class="breadcrumb-item active" aria-current="page">Gift Details</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm <?= $suggestionData['is_selected'] ? 'border-success' : '' ?>">
            <?php if ($suggestionData['is_selected']): ?>
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-check-circle me-1"></i> Selected Gift
                        </div>
                        <form action="index.php?page=suggestions&action=unselect" method="post" class="d-inline">
                            <input type="hidden" name="suggestion_id" value="<?= $suggestionData['suggestion_id'] ?>">
                            <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-times me-1"></i> Unselect
                            </button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Gift Details</h5>
                        <form action="index.php?page=suggestions&action=select" method="post" class="d-inline">
                            <input type="hidden" name="suggestion_id" value="<?= $suggestionData['suggestion_id'] ?>">
                            <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-check me-1"></i> Select This Gift
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4 text-center mb-3 mb-md-0">
                        <?php if (!empty($suggestionData['image_url'])): ?>
                            <img src="<?= h($suggestionData['image_url']) ?>" alt="<?= h($suggestionData['product_title']) ?>" class="img-fluid mb-3">
                        <?php else: ?>
                            <div class="gift-image bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-gift fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4 class="gift-price"><?= formatCurrency($suggestionData['price']) ?></h4>
                    </div>
                    <div class="col-md-8">
                        <h3><?= h($suggestionData['product_title']) ?></h3>
                        
                        <?php if (!empty($suggestionData['product_description'])): ?>
                            <div class="mt-3">
                                <h6>Description:</h6>
                                <p><?= nl2br(h($suggestionData['product_description'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="<?= h($suggestionData['amazon_url']) ?>" target="_blank" class="btn btn-primary">
                                <i class="fas fa-external-link-alt me-1"></i> View on Amazon
                            </a>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Gift For:</h6>
                        <p>
                            <strong><?= h($recipientData['name']) ?></strong><br>
                            <?php if (!empty($recipientData['relationship'])): ?>
                                <?= h($recipientData['relationship']) ?><br>
                            <?php endif; ?>
                            <?php if (!empty($recipientData['age'])): ?>
                                <?= h($recipientData['age']) ?> years old
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6>Occasion:</h6>
                        <p>
                            <strong><?= h($occasionData['occasion_type']) ?></strong><br>
                            <?= formatDate($occasionData['occasion_date']) ?><br>
                            <?= daysUntil($occasionData['occasion_date'], $occasionData['is_annual']) ?> days remaining
                        </p>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Product Details:</h6>
                    <ul>
                        <li><strong>Amazon ASIN:</strong> <?= h($suggestionData['amazon_asin']) ?></li>
                        <li><strong>Suggested On:</strong> <?= formatDate($suggestionData['created_at']) ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between">
                    <a href="index.php?page=suggestions&occasion_id=<?= $occasionData['occasion_id'] ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to All Suggestions
                    </a>
                    <?php if (!$suggestionData['is_selected']): ?>
                        <form action="index.php?page=suggestions&action=select" method="post">
                            <input type="hidden" name="suggestion_id" value="<?= $suggestionData['suggestion_id'] ?>">
                            <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Select This Gift
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden div for timestamps data -->
<div id="gift-timestamps" data-created="<?= h($createdAt) ?>" data-updated="<?= h($updatedAt) ?>" style="display: none;"></div>