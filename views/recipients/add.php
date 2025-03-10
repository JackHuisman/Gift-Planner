<?php
/**
 * Add Recipient form (Updated to capture only age)
 */

$pageTitle = 'Add Recipient';
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?page=recipients">Recipients</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Recipient</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Add New Recipient</h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=recipients&action=add" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= isset($_POST['name']) ? h($_POST['name']) : '' ?>">
                    </div>
                    
<div class="row mb-3">
    <div class="col-md-4">
        <label for="relationship" class="form-label">Relationship</label>
        <select class="form-select" id="relationship" name="relationship">
            <option value="">Select Relationship...</option>
            <option value="Family" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Family') ? 'selected' : '' ?>>Family</option>
            <option value="Friend" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Friend') ? 'selected' : '' ?>>Friend</option>
            <option value="Colleague" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Colleague') ? 'selected' : '' ?>>Colleague</option>
            <option value="Partner" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Partner') ? 'selected' : '' ?>>Partner</option>
            <option value="Spouse" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Spouse') ? 'selected' : '' ?>>Spouse</option>
            <option value="Child" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Child') ? 'selected' : '' ?>>Child</option>
            <option value="Parent" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Parent') ? 'selected' : '' ?>>Parent</option>
            <option value="Sibling" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Sibling') ? 'selected' : '' ?>>Sibling</option>
            <option value="Other" <?= (isset($_POST['relationship']) && $_POST['relationship'] === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
    </div>
    
    <div class="col-md-4">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select" id="gender" name="gender">
            <option value="">Select Gender...</option>
            <option value="Male" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : '' ?>>Other</option>
        </select>
    </div>
    
    <div class="col-md-4">
        <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="age" name="age" 
               min="0" max="120" step="1"
               value="<?= isset($_POST['age']) ? h($_POST['age']) : '' ?>" required>
    </div>
</div>
                    
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Interests, Preferences and Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Add interests, preferences, and other helpful information"><?= isset($_POST['notes']) ? h($_POST['notes']) : '' ?></textarea>
                        <div class="form-text">Include information that will help with gift selection, such as interests, hobbies, or clothing sizes.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=recipients" class="btn btn-outline-secondary me-md-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Recipient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>