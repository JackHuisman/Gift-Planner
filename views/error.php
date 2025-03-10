<?php
/**
 * 404 Not Found Page
 * Displayed when a user attempts to access a page that doesn't exist
 */

$pageTitle = 'Page Not Found';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6 text-center">
        <div class="card shadow-sm mb-4">
            <div class="card-body p-5">
                <div class="display-1 text-muted mb-4">
                    <i class="fas fa-map-signs"></i>
                </div>
                
                <h1 class="h2 mb-3">404 - Page Not Found</h1>
                <p class="lead mb-4">The page you're looking for doesn't exist or has been moved.</p>
                
                <img src="images/lost.svg" alt="Lost" class="img-fluid mb-4" style="max-height: 200px;">
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary me-md-2">
                        <i class="fas fa-arrow-left me-1"></i> Go Back
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info" role="alert">
            <p class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                If you typed the URL directly, please make sure the spelling is correct.
            </p>
        </div>
    </div>
</div>