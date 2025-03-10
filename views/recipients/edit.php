<?php
/**
 * Edit Recipient form (Updated with dynamic age calculation)
 */

$pageTitle = 'Edit Recipient';

// Ensure recipient exists and belongs to current user
if (!isset($recipientData) || $recipientData['user_id'] != $_SESSION['user_id']) {
    $errorTitle = 'Access Denied';
    $errorMessage = 'You do not have permission to edit this recipient.';
    include VIEWS_PATH . '/error.php';
    exit;
}

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
        <li class="breadcrumb-item"><a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>"><?= h($recipientData['name']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Edit Recipient: <?= h($recipientData['name']) ?></h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=recipients&action=edit" method="post">
                    <input type="hidden" name="recipient_id" value="<?= $recipientData['recipient_id'] ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= h($recipientData['name']) ?>">
                    </div>
                    
<div class="row mb-3">
    <div class="col-md-4">
        <label for="relationship" class="form-label">Relationship</label>
        <select class="form-select" id="relationship" name="relationship">
            <option value="">Select Relationship...</option>
            <option value="Family" <?= ($recipientData['relationship'] === 'Family') ? 'selected' : '' ?>>Family</option>
            <option value="Friend" <?= ($recipientData['relationship'] === 'Friend') ? 'selected' : '' ?>>Friend</option>
            <option value="Colleague" <?= ($recipientData['relationship'] === 'Colleague') ? 'selected' : '' ?>>Colleague</option>
            <option value="Partner" <?= ($recipientData['relationship'] === 'Partner') ? 'selected' : '' ?>>Partner</option>
            <option value="Spouse" <?= ($recipientData['relationship'] === 'Spouse') ? 'selected' : '' ?>>Spouse</option>
            <option value="Child" <?= ($recipientData['relationship'] === 'Child') ? 'selected' : '' ?>>Child</option>
            <option value="Parent" <?= ($recipientData['relationship'] === 'Parent') ? 'selected' : '' ?>>Parent</option>
            <option value="Sibling" <?= ($recipientData['relationship'] === 'Sibling') ? 'selected' : '' ?>>Sibling</option>
            <option value="Other" <?= ($recipientData['relationship'] === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
    </div>
    
    <div class="col-md-4">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select" id="gender" name="gender">
            <option value="">Select Gender...</option>
            <option value="Male" <?= ($recipientData['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($recipientData['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= ($recipientData['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
    </div>
    
    <div class="col-md-4">
        <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="age" name="age" 
               min="0" max="120" step="1"
               value="<?= isset($recipientData['display_age']) ? h($recipientData['display_age']) : '' ?>" required>
        <?php if (isset($recipientData['display_age']) && $recipientData['display_age'] != $recipientData['age']): ?>
            <div class="form-text text-info">
                Updated from <?= h($recipientData['age']) ?>
            </div>
        <?php endif; ?>
    </div>
</div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Interests, Preferences and Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add interests, preferences, and other helpful information"><?= h($recipientData['notes']) ?></textarea>
                        <div class="form-text">Include information that will help with gift selection, such as interests, hobbies, or clothing sizes.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Recipient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>