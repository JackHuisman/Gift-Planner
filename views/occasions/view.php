<?php
/**
 * View Occasion Details
 */

$pageTitle = 'Occasion Details';

// Get recipient information
$recipient = new Recipient($db);
$recipientData = $recipient->getById($occasionData['recipient_id']);

// Get specific date information for display
$specificDateInfo = '';

switch ($occasionData['occasion_type']) {
    case 'Birthday':
        if (!empty($occasionData['specific_date'])) {
            $month = date('F', strtotime($occasionData['specific_date']));
            $day = date('j', strtotime($occasionData['specific_date']));
            $specificDateInfo = "Birthday: $month $day";
        }
        break;
        
    case 'Anniversary':
        if (!empty($occasionData['specific_date'])) {
            $month = date('F', strtotime($occasionData['specific_date']));
            $day = date('j', strtotime($occasionData['specific_date']));
            $specificDateInfo = "Anniversary: $month $day";
        }
        break;
        
    case 'Christmas':
    case 'Valentine\'s Day':
    case 'Halloween':
    case 'New Year':
        $specificDateInfo = "Fixed annual holiday";
        break;
        
    case 'Mother\'s Day':
    case 'Father\'s Day':
    case 'Easter':
    case 'Thanksgiving':
        $specificDateInfo = "Variable annual holiday (date changes each year)";
        break;
}
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>"><?= h($recipientData['name']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= h($occasionData['occasion_type']) ?></li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0"><?= h($occasionData['occasion_type']) ?> for <?= h($recipientData['name']) ?></h1>
        <?php if (!empty($specificDateInfo)): ?>
            <p class="text-muted"><?= h($specificDateInfo) ?></p>
        <?php endif; ?>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="index.php?page=occasions&action=edit&id=<?= $occasionData['occasion_id'] ?>" class="btn btn-outline-primary me-2">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="index.php?page=suggestions&occasion_id=<?= $occasionData['occasion_id'] ?>" class="btn btn-primary">
            <i class="fas fa-gift me-1"></i> Gift Suggestions
        </a>
    </div>
</div>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Occasion updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Occasion Details</h5>
                
                <!-- Status Badge -->
                <span class="badge <?= $occasionData['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $occasionData['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Next Occurrence:</strong> <?= formatDate($occasionData['occasion_date']) ?></p>
                        <p class="mb-1">
                            <strong>Days Left:</strong> 
                            <span class="badge <?= getOccasionStatusClass(daysUntil($occasionData['occasion_date'], $occasionData['is_annual'])) ?> rounded-pill">
                                <?= daysUntil($occasionData['occasion_date'], $occasionData['is_annual']) ?> days
                            </span>
                        </p>
                        <p class="mb-1"><strong>Annual Event:</strong> <?= $occasionData['is_annual'] ? 'Yes' : 'No' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Budget:</strong> <?= formatCurrency($occasionData['price_min']) ?> - <?= formatCurrency($occasionData['price_max']) ?></p>
                        <p class="mb-1"><strong>Created:</strong> <?= formatDate($occasionData['created_at']) ?></p>
                        <p class="mb-1"><strong>Last Updated:</strong> <?= formatDate($occasionData['updated_at']) ?></p>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light">
                <?php if ($occasionData['is_active']): ?>
                    <form action="index.php?page=occasions&action=deactivate" method="post" class="d-inline">
                        <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to deactivate this occasion? You can reactivate it later if needed.')">
                            <i class="fas fa-ban me-1"></i> Deactivate Occasion
                        </button>
                    </form>
                <?php else: ?>
                    <form action="index.php?page=occasions&action=activate" method="post" class="d-inline">
                        <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-check me-1"></i> Reactivate Occasion
                        </button>
                    </form>
                <?php endif; ?>
                
                <form action="index.php?page=occasions&action=delete" method="post" class="d-inline float-end">
                    <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                    <input type="hidden" name="recipient_id" value="<?= $recipientData['recipient_id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to permanently delete this occasion? This action cannot be undone.')">
                        <i class="fas fa-trash-alt me-1"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Gift Suggestions Card --><!-- Gift Suggestions Card -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Gift Suggestions</h5>
        <a href="index.php?page=suggestions&occasion_id=<?= $occasionData['occasion_id'] ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-gift me-1"></i> View All
        </a>
    </div>
    <div class="card-body p-0">
        <?php
        // Get gift suggestions for this occasion
        $giftSuggestion = new GiftSuggestion($db);
        $suggestions = $giftSuggestion->getByOccasionId($occasionData['occasion_id']);
        
        if (empty($suggestions)):
        ?>
            <div class="p-4 text-center">
                <p class="text-muted mb-3">No gift suggestions available yet.</p>
                <?php if ($occasionData['is_active']): ?>
                    <a href="index.php?page=suggestions&action=generate&occasion_id=<?= $occasionData['occasion_id'] ?>" class="btn btn-outline-primary">
                        <i class="fas fa-magic me-1"></i> Generate Suggestions
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Gift</th>
                            <th>Price</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($suggestions, 0, 3) as $suggestion): ?>
                            <tr>
                                <td class="text-truncate" style="max-width: 300px;"><?= h($suggestion['product_title']) ?></td>
                                <td>
                                    <?php 
                                    // Handle possible different price formats
                                    if ($suggestion['price'] > 1000) { // Likely in cents
                                        echo formatCurrency($suggestion['price'] / 100);
                                    } else {
                                        echo formatCurrency($suggestion['price']);
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($suggestion['is_selected']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i> Selected</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?page=suggestions&action=view&id=<?= $suggestion['suggestion_id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= h($suggestion['amazon_url']) ?>" target="_blank" class="btn btn-sm btn-outline-success" title="View on Amazon">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (count($suggestions) > 3): ?>
                            <tr>
                                <td colspan="4" class="text-center">
                                    <a href="index.php?page=suggestions&occasion_id=<?= $occasionData['occasion_id'] ?>" class="text-decoration-none">
                                        View all <?= count($suggestions) ?> suggestions...
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
    </div>
    
    <div class="col-lg-4">
        <!-- Recipient Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Recipient Details</h5>
            </div>
            <div class="card-body">
                <h5><?= h($recipientData['name']) ?></h5>
                
                <div class="mb-3">
                    <?php if (!empty($recipientData['relationship'])): ?>
                        <span class="badge bg-primary"><?= h($recipientData['relationship']) ?></span>
                    <?php endif; ?>
                    
                    <?php if (!empty($recipientData['age'])): ?>
                        <span class="badge bg-secondary"><?= h($recipientData['age']) ?> years old</span>
                    <?php endif; ?>
                    
                    <?php if (!empty($recipientData['gender'])): ?>
                        <span class="badge bg-info text-dark"><?= h($recipientData['gender']) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($recipientData['notes'])): ?>
                    <h6>Interests, Preferences and Notes:</h6>
                    <p class="small"><?= nl2br(h($recipientData['notes'])) ?></p>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light">
                <a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-user me-1"></i> View Recipient
                </a>
            </div>
        </div>
        
        <!-- Other Occasions Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Other Occasions for <?= h($recipientData['name']) ?></h5>
            </div>
            <div class="card-body p-0">
                <?php
                // Get other occasions for this recipient
                $otherOccasions = $occasion->getByRecipientId($recipientData['recipient_id']);
                
                if (count($otherOccasions) <= 1):
                ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-0">No other occasions for this recipient.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($otherOccasions as $otherOccasion): ?>
                            <?php if ($otherOccasion['occasion_id'] != $occasionData['occasion_id']): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <a href="index.php?page=occasions&action=view&id=<?= $otherOccasion['occasion_id'] ?>" class="text-decoration-none">
                                            <?= h($otherOccasion['occasion_type']) ?>
                                        </a>
                                        <br>
                                        <small class="text-muted"><?= formatDate($otherOccasion['occasion_date']) ?></small>
                                    </div>
                                    
                                    <span class="badge <?= getOccasionStatusClass(daysUntil($otherOccasion['occasion_date'], $otherOccasion['is_annual'])) ?> rounded-pill">
                                        <?= daysUntil($otherOccasion['occasion_date'], $otherOccasion['is_annual']) ?> days
                                    </span>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-light">
                <a href="index.php?page=occasions&action=add&recipient_id=<?= $recipientData['recipient_id'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-calendar-plus me-1"></i> Add New Occasion
                </a>
            </div>
        </div>
    </div>
</div>