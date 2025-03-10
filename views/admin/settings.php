<?php
/**
 * Admin Settings Page
 * Allows an admin to manage system-wide settings
 */

$pageTitle = 'System Settings';

// Check if user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    $errorTitle = 'Access Denied';
    $errorMessage = 'You do not have permission to access this page.';
    include VIEWS_PATH . '/error.php';
    exit;
}

// Get all settings
$settings = new Settings($db);
$allSettings = $settings->getAll();

// Handle form submission
if (isset($_POST['save_settings'])) {
    $success = true;
    
    foreach ($_POST['settings'] as $key => $value) {
        if (!$settings->update($key, $value)) {
            $success = false;
        }
    }
    
    if ($success) {
        $successMessage = 'Settings saved successfully.';
    } else {
        $errorMessage = 'There was a problem saving the settings.';
    }
    
    // Refresh settings after update
    $allSettings = $settings->getAll();
}
?>

<div class="row mb-4">
    <div class="col">
        <h1 class="h3 mb-0">System Settings</h1>
    </div>
</div>

<?php if (isset($successMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= h($successMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= h($errorMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Application Settings</h5>
    </div>
    <div class="card-body">
        <form action="index.php?page=admin&action=settings" method="post">
            <div class="mb-4">
                <h6>Amazon Integration</h6>
                
                <?php
                $amazonSettings = array_filter($allSettings, function($setting) {
                    return strpos($setting['setting_key'], 'amazon_') === 0;
                });
                
                foreach ($amazonSettings as $setting):
                ?>
                    <div class="mb-3">
                        <label for="<?= $setting['setting_key'] ?>" class="form-label">
                            <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                        </label>
                        <input type="text" class="form-control" id="<?= $setting['setting_key'] ?>" 
                               name="settings[<?= $setting['setting_key'] ?>]" value="<?= h($setting['setting_value']) ?>">
                        <?php if (!empty($setting['description'])): ?>
                            <div class="form-text"><?= h($setting['description']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mb-4">
                <h6>Email Settings</h6>
                
                <?php
                $emailSettings = array_filter($allSettings, function($setting) {
                    return strpos($setting['setting_key'], 'email_') === 0;
                });
                
                foreach ($emailSettings as $setting):
                ?>
                    <div class="mb-3">
                        <label for="<?= $setting['setting_key'] ?>" class="form-label">
                            <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                        </label>
                        <input type="text" class="form-control" id="<?= $setting['setting_key'] ?>" 
                               name="settings[<?= $setting['setting_key'] ?>]" value="<?= h($setting['setting_value']) ?>">
                        <?php if (!empty($setting['description'])): ?>
                            <div class="form-text"><?= h($setting['description']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mb-4">
                <h6>Other Settings</h6>
                
                <?php
                $otherSettings = array_filter($allSettings, function($setting) {
                    return strpos($setting['setting_key'], 'amazon_') !== 0 && strpos($setting['setting_key'], 'email_') !== 0;
                });
                
                foreach ($otherSettings as $setting):
                ?>
                    <div class="mb-3">
                        <label for="<?= $setting['setting_key'] ?>" class="form-label">
                            <?= ucwords(str_replace('_', ' ', $setting['setting_key'])) ?>
                        </label>
                        <input type="text" class="form-control" id="<?= $setting['setting_key'] ?>" 
                               name="settings[<?= $setting['setting_key'] ?>]" value="<?= h($setting['setting_value']) ?>">
                        <?php if (!empty($setting['description'])): ?>
                            <div class="form-text"><?= h($setting['description']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" name="save_settings" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>