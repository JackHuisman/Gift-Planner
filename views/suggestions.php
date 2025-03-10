<?php
/**
 * Gift Suggestions list
 */

$pageTitle = 'Gift Suggestions';

// Get occasion and recipient data
$occasion = new Occasion($db);
$occasionData = $occasion->getById($occasionId);

$recipient = new Recipient($db);
$recipientData = $recipient->getById($occasionData['recipient_id']);
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
            Budget: <?= formatCurrency($occasionData['price_min']) ?> - <?= formatCurrency($occasionData['price_max']) ?>
            <span class="mx-2">•</span>
            Date: <?= formatDate($occasionData['occasion_date']) ?>
            <span class="mx-2">•</span>
            Days Left: <?= daysUntil($occasionData['occasion_date'], $occasionData['is_annual']) ?>
        </p>
    </div>
    <div class="col-md-4 text-md-end">
        <button class="btn btn-primary" id="generateSuggestions" data-occasion-id="<?= $occasionData['occasion_id'] ?>">
            <i class="fas fa-sync me-1"></i> Refresh Suggestions
        </button>
    </div>
</div>

<?php if (isset($_GET['selected']) && $_GET['selected'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Gift has been marked as selected!
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
            <p class="text-muted mb-4">Gift suggestions will be generated automatically 2 weeks before the occasion date.</p>
            <button class="btn btn-primary" id="generateSuggestionsEmpty" data-occasion-id="<?= $occasionData['occasion_id'] ?>">
                <i class="fas fa-sync me-1"></i> Generate Suggestions Now
            </button>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-4 g-4">
        <?php foreach ($suggestions as $suggestion): ?>
            <div class="col">
                <div class="card h-100 <?= $suggestion['is_selected'] ? 'border-success' : '' ?>">
                    <?php if (!empty($suggestion['image_url'])): ?>
                        <img src="<?= h($suggestion['image_url']) ?>" class="card-img-top p-2" alt="<?= h($suggestion['product_title']) ?>">
                    <?php else: ?>
                        <div class="card-img-top bg-light text-center py-4">
                            <i class="fas fa-gift fa-4x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= h($suggestion['product_title']) ?></h5>
                        <p class="card-text text-primary fw-bold"><?= formatCurrency($suggestion['price']) ?></p>
                        
                        <?php if (!empty($suggestion['product_description'])): ?>
                            <p class="card-text small">
                                <?= nl2br(h(substr($suggestion['product_description'], 0, 150))) ?>
                                <?= strlen($suggestion['product_description']) > 150 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($suggestion['is_selected']): ?>
                            <span class="badge bg-success mb-2">Selected</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-grid gap-2">
                            <a href="<?= h($suggestion['amazon_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i> View on Amazon
                            </a>
                            
                            <?php if (!$suggestion['is_selected']): ?>
                                <form action="index.php?page=suggestions&action=select" method="post">
                                    <input type="hidden" name="suggestion_id" value="<?= $suggestion['suggestion_id'] ?>">
                                    <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="fas fa-check me-1"></i> Select This Gift
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Generate Suggestions Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const generateBtns = document.querySelectorAll('#generateSuggestions, #generateSuggestionsEmpty');
    
    generateBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const occasionId = this.getAttribute('data-occasion-id');
            
            // Show loading state
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
            this.disabled = true;
            
            // In a real implementation, this would be an AJAX call to a backend endpoint
            // For this demo, we'll simulate a delay and reload the page
            setTimeout(function() {
                window.location.reload();
            }, 2000);
        });
    });
});
</script>