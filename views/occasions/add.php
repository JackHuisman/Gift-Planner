<?php
/**
 * Add Occasion form with special handling for birthdays
 */

$pageTitle = 'Add Occasion';
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>"><?= h($recipientData['name']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Occasion</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Add New Occasion for <?= h($recipientData['name']) ?></h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=occasions&action=add" method="post">
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
                               value="<?= isset($_POST['occasion_date']) ? h($_POST['occasion_date']) : '' ?>">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_annual" name="is_annual" value="1" 
                               <?= (!isset($_POST['is_annual']) || $_POST['is_annual']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_annual">This is an annual event</label>
                        <div class="form-text">Check this for recurring occasions like birthdays and anniversaries.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price_min" class="form-label">Minimum Budget <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price_min" name="price_min" min="1" step="1" required
                                       value="<?= isset($_POST['price_min']) ? h($_POST['price_min']) : '20' ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="price_max" class="form-label">Maximum Budget <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price_max" name="price_max" min="1" step="1" required
                                       value="<?= isset($_POST['price_max']) ? h($_POST['price_max']) : '100' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div id="price_range_display" class="mb-3">
                        <label class="form-label">Budget Range</label>
                        <div class="alert alert-info">
                            <span id="price_range_text">$20 - $100</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Gift Preferences (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add specific gift preferences for this occasion"><?= isset($_POST['notes']) ? h($_POST['notes']) : '' ?></textarea>
                        <div class="form-text">E.g., "Likes mystery novels" or "No electronics please"</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=recipients&action=view&id=<?= $recipientData['recipient_id'] ?>" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Occasion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const occasionType = document.getElementById('occasion_type');
    const occasionDate = document.getElementById('occasion_date');
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
    
    // Set default date based on occasion type
    function setDefaultDate() {
        const selectedType = occasionType.value;
        
        // Clear any existing date when changing occasion types
        if (selectedType) {
            occasionDate.value = '';
        }
        
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        let defaultDate = null;
        
        switch (selectedType) {
            case 'Christmas':
                // Set to December 25 of current year
                defaultDate = new Date(currentYear, 11, 25); // Month is 0-based (11 = December)
                break;
                
            case 'Valentine\'s Day':
                // Set to February 14 of current year
                defaultDate = new Date(currentYear, 1, 14); // Month is 0-based (1 = February)
                break;
                
            case 'New Year':
                // Set to January 1 of next year
                defaultDate = new Date(currentYear + 1, 0, 1); // Month is 0-based (0 = January)
                break;
                
            case 'Halloween':
                // Set to October 31 of current year
                defaultDate = new Date(currentYear, 9, 31); // Month is 0-based (9 = October)
                break;
                
            case 'Mother\'s Day':
                // Second Sunday in May
                defaultDate = calculateNthDayOfMonth(currentYear, 4, 0, 2); // May (4), Sunday (0), 2nd occurrence
                break;
                
            case 'Father\'s Day':
                // Third Sunday in June
                defaultDate = calculateNthDayOfMonth(currentYear, 5, 0, 3); // June (5), Sunday (0), 3rd occurrence
                break;
                
            case 'Thanksgiving':
                // Fourth Thursday in November (USA)
                defaultDate = calculateNthDayOfMonth(currentYear, 10, 4, 4); // November (10), Thursday (4), 4th occurrence
                break;
                
            case 'Easter':
                // Easter is complex - use an approximation or hardcode for a few years
                // Here's a simple approximation (usually in April)
                defaultDate = new Date(currentYear, 3, 10); // April 10th as placeholder
                break;
                
            default:
                // For other types, don't set a default date
                return;
        }
        
        // If the default date has already passed this year, use next year instead
        if (defaultDate !== null) {
            if (defaultDate < currentDate) {
                if (selectedType === 'Easter') {
                    // For Easter, just add a year to our approximation
                    defaultDate = new Date(currentYear + 1, 3, 10);
                } else if (['Mother\'s Day', 'Father\'s Day', 'Thanksgiving'].includes(selectedType)) {
                    // For variable holidays based on day of week, recalculate for next year
                    const month = defaultDate.getMonth();
                    const dayOfWeek = defaultDate.getDay();
                    const occurrence = selectedType === 'Mother\'s Day' ? 2 : 
                                      selectedType === 'Father\'s Day' ? 3 : 4;
                    defaultDate = calculateNthDayOfMonth(currentYear + 1, month, dayOfWeek, occurrence);
                } else {
                    // For fixed date holidays, just change the year
                    defaultDate.setFullYear(currentYear + 1);
                }
            }
            
            // Format the date as YYYY-MM-DD for the input field
            const formattedDate = defaultDate.toISOString().split('T')[0];
            occasionDate.value = formattedDate;
        }
    }
    
    // Helper function to calculate nth occurrence of a day in a month
    // month is 0-11, dayOfWeek is 0-6 (Sunday-Saturday), n is which occurrence (1-5)
    function calculateNthDayOfMonth(year, month, dayOfWeek, n) {
        let date = new Date(year, month, 1);
        let count = 0;
        
        // Find the first occurrence of the specified day in the month
        while (date.getDay() !== dayOfWeek) {
            date.setDate(date.getDate() + 1);
        }
        
        // Add weeks to get to the nth occurrence
        date.setDate(date.getDate() + (n - 1) * 7);
        
        return date;
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
    
    // Add event listeners
    occasionType.addEventListener('change', function() {
        toggleEventDateField();
        setDefaultDate(); // Set default date when occasion type changes
    });
    
    priceMin.addEventListener('input', updatePriceRange);
    priceMax.addEventListener('input', updatePriceRange);
    
    // Initial checks
    toggleEventDateField();
    updatePriceRange();
    
    // If an occasion type is already selected (e.g., from a previous form submission),
    // set the default date only if the date field is empty
    if (occasionType.value && !occasionDate.value) {
        setDefaultDate();
    }
});
</script>