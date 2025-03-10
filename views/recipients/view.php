<?php
/**
 * Recipient Details page (Updated with dynamic age calculation)
 */

$pageTitle = 'Recipient Details';

// Capture creation and update timestamps (but don't display them)
$createdAt = $recipientData['created_at'] ?? null;
$updatedAt = $recipientData['updated_at'] ?? null;

// Calculate current age based on stored age and time elapsed since last update
if (!empty($recipientData['age']) && !empty($recipientData['updated_at'])) {
    $lastUpdateDate = new DateTime($recipientData['updated_at']);
    $currentDate = new DateTime();
    $yearDiff = $currentDate->diff($lastUpdateDate)->y;
    $currentAge = $recipientData['age'] + $yearDiff;
    $recipientData['display_age'] = $currentAge;
} else {
    $recipientData['display_age'] = $recipientData['age'] ?? null;
}
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= h($recipientData['name']) ?></li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0"><?= h($recipientData['name']) ?></h1>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="index.php?page=recipients&action=edit&id=<?= $recipientData['recipient_id'] ?>" class="btn btn-outline-primary me-2">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="index.php?page=occasions&action=add&recipient_id=<?= $recipientData['recipient_id'] ?>" class="btn btn-primary">
            <i class="fas fa-calendar-plus me-1"></i> Add Occasion
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Recipient Details</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($recipientData['relationship'])): ?>
                        <li class="list-group-item px-0">
                            <strong>Relationship:</strong> <?= h($recipientData['relationship']) ?>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($recipientData['gender'])): ?>
                        <li class="list-group-item px-0">
                            <strong>Gender:</strong> <?= h($recipientData['gender']) ?>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($recipientData['display_age'])): ?>
                        <li class="list-group-item px-0">
                            <strong>Age:</strong> <?= h($recipientData['display_age']) ?> years
                            <?php if ($recipientData['display_age'] != $recipientData['age']): ?>
                                <small class="text-muted">(Updated from <?= h($recipientData['age']) ?> years stored in database)</small>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Created and updated timestamps are captured but not displayed -->
                </ul>
                
                <?php if (!empty($recipientData['notes'])): ?>
                    <div class="mt-3">
                        <h6>Interests, Preferences and Notes:</h6>                
                        <p class="mb-0"><?= nl2br(h($recipientData['notes'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Occasions</h5>
                <a href="index.php?page=occasions&action=add&recipient_id=<?= $recipientData['recipient_id'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Occasion
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($occasions)): ?>
                    <div class="p-4 text-center">
                        <p class="text-muted mb-3">No occasions added for this recipient yet.</p>
                        <a href="index.php?page=occasions&action=add&recipient_id=<?= $recipientData['recipient_id'] ?>" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-plus me-1"></i> Add First Occasion
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Occasion</th>
                                    <th>Date</th>
                                    <th>Days Left</th>
                                    <th>Budget</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($occasions as $occasion): ?>
                                    <?php 
                                    $daysUntil = daysUntil($occasion['occasion_date'], $occasion['is_annual']);
                                    $statusClass = getOccasionStatusClass($daysUntil);
                                    ?>
                                    <tr>
                                        <td><?= h($occasion['occasion_type']) ?></td>
                                        <td><?= formatDate($occasion['occasion_date']) ?></td>
                                        <td>
                                            <span class="badge <?= $statusClass ?> rounded-pill">
                                                <?= $daysUntil ?> day<?= $daysUntil !== 1 ? 's' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= formatCurrency($occasion['price_min']) ?> - <?= formatCurrency($occasion['price_max']) ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=occasions&action=view&id=<?= $occasion['occasion_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary me-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?page=suggestions&occasion_id=<?= $occasion['occasion_id'] ?>" 
                                               class="btn btn-sm btn-outline-success" title="View Gift Suggestions">
                                                <i class="fas fa-gift"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Hidden div with the timestamps data for potential JS usage -->
<div id="recipient-timestamps" data-created="<?= h($createdAt) ?>" data-updated="<?= h($updatedAt) ?>" style="display: none;"></div>