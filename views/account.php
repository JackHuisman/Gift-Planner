<?php
/**
 * Account Settings Page
 */

$pageTitle = 'Account Settings';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Account Settings</h1>
    </div>
</div>

<?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Your account settings have been updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-4 mb-4">
        <!-- Account Summary Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Account Summary</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="display-1 text-muted">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h4 class="mt-2 mb-0"><?= h($userData['username']) ?></h4>
                    <p class="text-muted"><?= h($userData['email']) ?></p>
                </div>
                
                <hr>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Account Created</span>
                        <span class="text-muted"><?= formatDate($userData['created_at']) ?></span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                        <span>Last Updated</span>
                        <span class="text-muted"><?= formatDate($userData['updated_at']) ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Account Stats Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Your Statistics</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Recipients</span>
                        <?php
                        $recipient = new Recipient($db);
                        $recipientsCount = count($recipient->getByUserId($_SESSION['user_id']));
                        ?>
                        <span class="badge bg-primary rounded-pill"><?= $recipientsCount ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Occasions</span>
                        <?php
                        $occasion = new Occasion($db);
                        $occasionsCount = $occasion->countByUserId($_SESSION['user_id']);
                        ?>
                        <span class="badge bg-info rounded-pill"><?= $occasionsCount ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Gift Suggestions</span>
                        <?php
                        $giftSuggestion = new GiftSuggestion($db);
                        $suggestionsCount = $giftSuggestion->countForUser($_SESSION['user_id']);
                        ?>
                        <span class="badge bg-secondary rounded-pill"><?= $suggestionsCount ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Selected Gifts</span>
                        <?php
                        $selectedCount = $giftSuggestion->countSelectedByUserId($_SESSION['user_id']);
                        ?>
                        <span class="badge bg-success rounded-pill"><?= $selectedCount ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-lg-8">
        <!-- Account Settings Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Account Settings</h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=account&action=update" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?= h($userData['username']) ?>" disabled>
                        <div class="form-text">Username cannot be changed.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" value="<?= h($userData['email']) ?>" disabled>
                        <div class="form-text">To change your email, please contact support.</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <p class="mb-0"><i class="fas fa-info-circle me-2"></i> When gifts are purchased through this application, the app creator earns a small commission from Amazon at no additional cost to you.</p>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Change Password Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form action="index.php?page=account&action=change_password" method="post">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required
                               pattern=".{8,}" title="Password must be at least 8 characters">
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-1"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Privacy & Data Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Privacy & Data</h5>
            </div>
            <div class="card-body">
                <p>Manage your data privacy settings and account deletion options.</p>
                
                <div class="d-grid gap-2 d-md-flex">
                    <a href="#" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#exportDataModal">
                        <i class="fas fa-download me-1"></i> Export My Data
                    </a>
                    <a href="#" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash-alt me-1"></i> Delete Account
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Data Modal -->
<div class="modal fade" id="exportDataModal" tabindex="-1" aria-labelledby="exportDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportDataModalLabel">Export Your Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You can download all your personal data in a convenient format. This includes:</p>
                <ul>
                    <li>Account information</li>
                    <li>Recipients list</li>
                    <li>Occasions and dates</li>
                    <li>Gift suggestions and selections</li>
                </ul>
                <p>The export process may take a few moments to complete.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="index.php?page=account&action=export_data" class="btn btn-primary">
                    <i class="fas fa-download me-1"></i> Download Data
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Delete Your Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i> Warning!</h5>
                    <p>Deleting your account is permanent and cannot be undone. All your data will be permanently removed.</p>
                </div>
                
                <p>This includes:</p>
                <ul>
                    <li>Your account information</li>
                    <li>All recipients and their details</li>
                    <li>All occasions and dates</li>
                    <li>All gift suggestions and selections</li>
                </ul>
                
                <form id="delete-account-form" action="index.php?page=account&action=delete" method="post">
                    <div class="mb-3">
                        <label for="delete_confirmation" class="form-label">Type "DELETE" to confirm</label>
                        <input type="text" class="form-control" id="delete_confirmation" name="delete_confirmation" required
                               pattern="DELETE" title="Please type DELETE in all caps">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Enter your password to confirm</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="delete-account-form" class="btn btn-danger" onclick="return confirm('WARNING: This action cannot be undone. Are you absolutely sure you want to permanently delete your account?');">
                    <i class="fas fa-trash-alt me-1"></i> Permanently Delete Account
                </button>
            </div>
        </div>
    </div>
</div>