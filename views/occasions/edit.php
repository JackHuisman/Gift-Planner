<?php
/**
 * Edit Occasion form
 */

$pageTitle = 'Edit Occasion';

// Get recipient information
$recipient = new Recipient($db);
$recipientData = $recipient->getById($occasionData['recipient_id']);
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>"><?= h($recipientData['name']) ?></a></li>
        <li class="breadcrumb-item"><a href="index.php?page=occasions&action=view&id=<?= $occasionData['occasion_id'] ?>"><?= h($occasionData['occasion_type']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Edit Occasion for <?= h($recipientData['name']) ?></h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=occasions&action=update" method="post">
                    <input type="hidden" name="occasion_id" value="<?= $occasionData['occasion_id'] ?>">
                    <input type="hidden" name="recipient_id" value="<?= $recipientData['recipient_id'] ?>">
                    
<div class="mb-3">
    <label for="occasion_type" class="form-label">Occasion Type <span class="text-danger">*</span></label>
    <select class="form-select" id="occasion_type" name="occasion_type" required>
        <option value="">Select Occasion Type...</option>
        <option value="Birthday" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Birthday') ? 'selected' : '' ?>>Birthday</option>
        <option value="Anniversary" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Anniversary') ? 'selected' : '' ?>>Anniversary</option>
        <option value="Christmas" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Christmas') ? 'selected' : '' ?>>Christmas</option>
        <option value="Valentine's Day" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === "Valentine's Day") ? 'selected' : '' ?>>Valentine's Day</option>
        <option value="Mother's Day" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === "Mother's Day") ? 'selected' : '' ?>>Mother's Day</option>
        <option value="Father's Day" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === "Father's Day") ? 'selected' : '' ?>>Father's Day</option>
        <option value="Easter" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Easter') ? 'selected' : '' ?>>Easter</option>
        <option value="Graduation" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Graduation') ? 'selected' : '' ?>>Graduation</option>
        <option value="Wedding" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Wedding') ? 'selected' : '' ?>>Wedding</option>
        <option value="Baby Shower" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Baby Shower') ? 'selected' : '' ?>>Baby Shower</option>
        <option value="Other" <?= (isset($occasionData['occasion_type']) && $occasionData['occasion_type'] === 'Other') ? 'selected' : '' ?>>Other</option>
    </select>
</div>

                    
                    <div id="event_date_field" class="mb-3" style="display: none;">
                        <label for="event_date" class="form-label">Event Date (Birth, wedding, etc.) <span class="text-danger">*</span></label>
    <input type="date" class="form-control" id="event_date" name="specific_date" 
           value="<?= isset($occasionData['specific_date']) ? date('Y-m-d', strtotime($occasionData['specific_date'])) : '' ?>">
                        <div class="form-text">For birthdays, anniversaries, and one-time events occurring on a specific date.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="occasion_date" class="form-label">Gift Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="occasion_date" name="occasion_date" required
                               value="<?= date('Y-m-d', strtotime($occasionData['occasion_date'])) ?>">
                    </div>
                    
<!--<div id="event_date_field" class="mb-3" <?= (in_array($occasionData['occasion_type'], ['Birthday', 'Anniversary', 'Other'])) ? '' : 'style="display: none;"' ?>>
    <label for="event_date" class="form-label">Event Date (one-time event) <span class="text-danger">*</span></label>
    <input type="date" class="form-control" id="event_date" name="specific_date" 
           value="<?= !empty($occasionData['specific_date']) ? date('Y-m-d', strtotime($occasionData['specific_date'])) : '' ?>">
    <div class="form-text">For birthdays, anniversaries, and one-time events occurring on a specific date.</div>
     Debug info (remove after fixing) 
    <div class="text-muted small mt-1">
        Specific date from DB: <?= var_export($occasionData['specific_date'], true) ?>
    </div>
</div>-->


                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_annual" name="is_annual" value="1" 
                               <?= $occasionData['is_annual'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_annual">This is an annual event</label>
                        <div class="form-text">Check this for recurring occasions like birthdays and anniversaries.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price_min" class="form-label">Minimum Budget <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price_min" name="price_min" min="1" step="1" required
                                       value="<?= $occasionData['price_min'] ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="price_max" class="form-label">Maximum Budget <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price_max" name="price_max" min="1" step="1" required
                                       value="<?= $occasionData['price_max'] ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div id="price_range_display" class="mb-3">
                        <label class="form-label">Budget Range</label>
                        <div class="alert alert-info">
                            <span id="price_range_text">$<?= $occasionData['price_min'] ?> - $<?= $occasionData['price_max'] ?></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Gift Preferences (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add specific gift preferences for this occasion"><?= h($occasionData['notes'] ?? '') ?></textarea>
                        <div class="form-text">E.g., "Likes mystery novels" or "No electronics please"</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=occasions&action=view&id=<?= $occasionData['occasion_id'] ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Occasion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const occasionType = document.getElementById('occasion_type');
    const eventDateField = document.getElementById('event_date_field');
    const priceMin = document.getElementById('price_min');
    const priceMax = document.getElementById('price_max');
    const priceRangeText = document.getElementById('price_range_text');
    
    // Show/hide event date field based on selected occasion type
    function toggleEventDateField() {
        if (occasionType.value === 'Birthday' || occasionType.value === 'Anniversary' || occasionType.value === 'Other') {
            eventDateField.style.display = 'block';
        } else {
            eventDateField.style.display = 'none';
        }
    }
    
    // Update price range text
    function updatePriceRange() {
        const min = parseFloat(priceMin.value) || 0;
        const max = parseFloat(priceMax.value) || 0;
        
        // Ensure min doesn't exceed max
        if (min > max) {
            priceMax.value = min;
        }
        
        priceRangeText.textContent = `$${priceMin.value} - $${priceMax.value}`;
    }
    
    // Initial checks
    toggleEventDateField();
    updatePriceRange();
    
    // Add event listeners
    occasionType.addEventListener('change', toggleEventDateField);
    priceMin.addEventListener('input', updatePriceRange);
    priceMax.addEventListener('input', updatePriceRange);
});
</script>