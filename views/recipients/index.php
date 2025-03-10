<?php
/**
 * Recipients list page with dynamic age calculation
 */
$pageTitle = 'My Recipients';

// Process recipients to update their displayed ages
foreach ($recipients as &$recipient) {
    $recipient['display_age'] = calculateCurrentAge($recipient['age'], $recipient['updated_at']);
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0">My Recipients</h1>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="index.php?page=recipients&action=add" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Add Recipient
        </a>
    </div>
</div>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Recipient added successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Recipient updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (empty($recipients)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="display-1 text-muted mb-3">
                <i class="fas fa-users"></i>
            </div>
            <h3>No Recipients Added Yet</h3>
            <p class="text-muted mb-4">Start by adding your friends and family members.</p>
            <a href="index.php?page=recipients&action=add" class="btn btn-primary">
                <i class="fas fa-user-plus me-1"></i> Add Your First Recipient
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($recipients as $recipient): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title mb-0"><?= h($recipient['name']) ?></h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=recipients&action=view&id=<?= $recipient['recipient_id'] ?>">
                                            <i class="fas fa-eye me-2"></i> View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=recipients&action=edit&id=<?= $recipient['recipient_id'] ?>">
                                            <i class="fas fa-edit me-2"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="index.php?page=occasions&action=add&recipient_id=<?= $recipient['recipient_id'] ?>">
                                            <i class="fas fa-calendar-plus me-2"></i> Add Occasion
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteRecipientModal<?= $recipient['recipient_id'] ?>">
                                            <i class="fas fa-trash-alt me-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <?php if (!empty($recipient['relationship'])): ?>
                                <span class="badge bg-primary"><?= h($recipient['relationship']) ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($recipient['display_age'])): ?>
                                <span class="badge bg-secondary"><?= h($recipient['display_age']) ?> years old</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($recipient['gender'])): ?>
                                <span class="badge bg-info text-dark"><?= h($recipient['gender']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($recipient['notes'])): ?>
                            <p class="text-muted mb-1"><small><strong>Interests, Preferences and Notes:</strong></small></p>
        <p class="card-text small text-muted">
                                <?= nl2br(h(substr($recipient['notes'], 0, 100))) ?>
                                <?= strlen($recipient['notes']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php
                        // Get upcoming occasions for this recipient
                        $occasion = new Occasion($db);
                        $recipientOccasions = $occasion->getByRecipientId($recipient['recipient_id']);
                        $upcomingCount = 0;
                        $nextOccasion = null;
                        
                        foreach ($recipientOccasions as $occ) {
                            $daysUntil = daysUntil($occ['occasion_date'], $occ['is_annual']);
                            if ($daysUntil <= 30) {
                                $upcomingCount++;
                                if ($nextOccasion === null || $daysUntil < daysUntil($nextOccasion['occasion_date'], $nextOccasion['is_annual'])) {
                                    $nextOccasion = $occ;
                                }
                            }
                        }
                        ?>
                    </div>
                    <div class="card-footer bg-light">
                        <?php if ($nextOccasion): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">Next: <?= h($nextOccasion['occasion_type']) ?></small>
                                    <br>
                                    <small class="text-muted"><?= formatDate($nextOccasion['occasion_date']) ?></small>
                                </div>
                                <span class="badge <?= getOccasionStatusClass(daysUntil($nextOccasion['occasion_date'], $nextOccasion['is_annual'])) ?> rounded-pill">
                                    <?= daysUntil($nextOccasion['occasion_date'], $nextOccasion['is_annual']) ?> days
                                </span>
                            </div>
                        <?php else: ?>
                            <small class="text-muted">No upcoming occasions</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Delete Recipient Modal -->
                <div class="modal fade" id="deleteRecipientModal<?= $recipient['recipient_id'] ?>" tabindex="-1" aria-labelledby="deleteRecipientModalLabel<?= $recipient['recipient_id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteRecipientModalLabel<?= $recipient['recipient_id'] ?>">Confirm Deletion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete <strong><?= h($recipient['name']) ?></strong>?</p>
                                <p class="text-danger"><small>This will also delete all occasions and gift suggestions associated with this recipient.</small></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form action="index.php?page=recipients&action=delete" method="post">
                                    <input type="hidden" name="recipient_id" value="<?= $recipient['recipient_id'] ?>">
                                    <button type="submit" class="btn btn-danger">Delete Recipient</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>