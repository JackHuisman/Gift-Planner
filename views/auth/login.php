<?php
/**
 * Login page
 */

$pageTitle = 'Login';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h1 class="h3">Welcome Back</h1>
                    <p class="text-muted">Login to manage your gift planning</p>
                </div>
                
                <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
                    <div class="alert alert-success">
                        Your account has been created successfully. Please log in.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['logged_out']) && $_GET['logged_out'] == 1): ?>
                    <div class="alert alert-info">
                        You have been logged out successfully.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['password_reset']) && $_GET['password_reset'] == 1): ?>
                    <div class="alert alert-success">
                        Your password has been reset successfully. Please log in with your new password.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($loginError)): ?>
                    <div class="alert alert-danger">
                        <?= h($loginError) ?>
                    </div>
                <?php endif; ?>
                
                <form action="index.php?page=login&action=authenticate" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus
                               value="<?= isset($_COOKIE['remember_email']) ? h($_COOKIE['remember_email']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                               <?= isset($_COOKIE['remember_email']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </div>
                    
                    <div class="text-center">
                        <a href="index.php?page=forgot-password" class="text-decoration-none">Forgot your password?</a>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-light py-3 text-center">
                Don't have an account? <a href="index.php?page=register" class="text-decoration-none">Register now</a>
            </div>
        </div>
    </div>
</div>