<?php
/**
 * Gift Suggestions Page
 * Shows gift suggestions for a specific occasion
 */

$pageTitle = 'Gift Suggestions';

// Get occasion and recipient information
$occasion = new Occasion($db);
$occasionData = $occasion->getById($occasionId);

$recipient = new Recipient($db);
$recipientData = $recipient->getById($occasionData['recipient_id']);

// Get gift suggestions for this occasion
$giftSuggestion = new GiftSuggestion($db);
$suggestions = $giftSuggestion->getByOccasionId($occasionData['occasion_id']);

// Capture timestamps but don't display them
$occasionCreatedAt = $occasionData['created_at'] ?? null;
$occasionUpdatedAt = $occasionData['updated_at'] ?? null;
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>"><?= h($recipientData['name']) ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=occasions&action=view&id=<?= $occasionData['occasion_id'] ?>"><?= h($occasionData['occasion_type']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Gift Suggestions</li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0">Gift Suggestions for <?= h($recipientData['name']) ?>'s <?= h($occasionData['occasion_type']) ?></h1>
        <p class="text-muted">
            <?= formatDate($occasionData['occasion_date']) ?> 
            (<?= daysUntil($occasionData['occasion_date'], $occasionData['is_annual']) ?> days remaining)
        </p>
    </div>
    <div class="col-md-4 text-md-end">
        <?php if (!empty($suggestions)): ?>
        <a href="#suggestionsFooter" class="btn btn-outline-primary">
            <i class="fas fa-arrow-down me-1"></i> View All Suggestions
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['selected']) && $_GET['selected'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Gift selection saved successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['generated']) && $_GET['generated'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Gift suggestions have been successfully generated!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (empty($suggestions)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="display-1 text-muted mb-3">
                <i class="fas fa-gift"></i>
            </div>
            <h3>No Gift Suggestions Yet</h3>
            <p class="text-muted mb-4">We haven't generated gift suggestions for this occasion yet.</p>
            <a href="index.php?page=suggestions&action=generate&occasion_id=<?= $occasionData['occasion_id'] ?>" class="btn btn-primary">
                <i class="fas fa-magic me-1"></i> Generate Suggestions
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Hidden form for gift selection -->
    <form id="gift-selection-form" action="index.php?page=suggestions&action=select" method="post">
        <input type="hidden" id="suggestion_id" name="suggestion_id" value="">
        <input type="hidden" id="occasion_id" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
    </form>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Recipient Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Name:</strong> <?= h($recipientData['name']) ?></p>
                            <?php if (!empty($recipientData['relationship'])): ?>
                                <p class="mb-1"><strong>Relationship:</strong> <?= h($recipientData['relationship']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <?php if (!empty($recipientData['gender'])): ?>
                                <p class="mb-1"><strong>Gender:</strong> <?= h($recipientData['gender']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($recipientData['age'])): ?>
                                <p class="mb-1"><strong>Age:</strong> <?= h($recipientData['age']) ?> years</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Budget:</strong> <?= formatCurrency($occasionData['price_min']) ?> - <?= formatCurrency($occasionData['price_max']) ?></p>
                        </div>
                    </div>
                    
                    <?php if (!empty($recipientData['notes'])): ?>
                        <hr>
                        <h6>Interests, Preferences and Notes:</h6>
                        <p class="mb-0"><?= nl2br(h($recipientData['notes'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
        <?php foreach ($suggestions as $suggestion): ?>
            <div class="col">
                <div class="card h-100 shadow-sm gift-card">
                    <?php if (!empty($suggestion['image_url'])): ?>
                        <div class="gift-image" style="background-image: url('<?= h($suggestion['image_url']) ?>')"></div>
                    <?php else: ?>
                        <div class="gift-image" style="background-image: url('images/gift-placeholder.jpg')"></div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title text-truncate-2"><?= h($suggestion['product_title']) ?></h5>
                        
                        <p class="gift-price mb-3">
                            <?php 
                            // Handle possible different price formats
                            if ($suggestion['price'] > 1000) { // Likely in cents
                                echo formatCurrency($suggestion['price'] / 100);
                            } else {
                                echo formatCurrency($suggestion['price']);
                            }
                            ?>
                        </p>
                        
                        <?php if (!empty($suggestion['product_description'])): ?>
                            <p class="card-text small text-muted">
                                <?= nl2br(h(substr($suggestion['product_description'], 0, 150))) ?>
                                <?= strlen($suggestion['product_description']) > 150 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-footer bg-white d-grid gap-2">
                        <a href="<?= h($suggestion['amazon_url']) ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i> View on Amazon
                        </a>
                        
                        <?php if ($suggestion['is_selected']): ?>
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check me-1"></i> Selected
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-success gift-select-btn" 
                                    data-suggestion-id="<?= $suggestion['suggestion_id'] ?>"
                                    data-occasion-id="<?= $occasionId ?>">
                                <i class="fas fa-check me-1"></i> Select This Gift
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="card shadow-sm" id="suggestionsFooter">
        <div class="card-body bg-light">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-md-0">
                        <strong>Need more options?</strong> You can refresh the suggestions to get new ideas based on the recipient's profile.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="index.php?page=suggestions&action=refresh&occasion_id=<?= $occasionData['occasion_id'] ?>" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-1"></i> Refresh Suggestions
                    </a>
                </div>
            </div>
        </div>
    </div>
    
<?php endif; ?>

<!-- Hidden div for timestamps data -->
<div id="occasion-timestamps" data-created="<?= h($occasionCreatedAt) ?>" data-updated="<?= h($occasionUpdatedAt) ?>" style="display: none;"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gift selection handling
    const giftSelectionButtons = document.querySelectorAll('.gift-select-btn');
    
    giftSelectionButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const suggestionId = this.getAttribute('data-suggestion-id');
            const occasionId = this.getAttribute('data-occasion-id');
            
            // Set the form values
            document.getElementById('suggestion_id').value = suggestionId;
            
            // Submit the form
            document.getElementById('gift-selection-form').submit();
        });
    });
});
</script>