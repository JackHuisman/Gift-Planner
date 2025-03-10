<?php
/**
 * Header template
 * Displays the header section of the application, including navigation
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?>Gift Planner</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-gift me-2"></i>Gift Planner
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $page === 'recipients' ? 'active' : '' ?>" href="index.php?page=recipients">Recipients</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $page === 'account' ? 'active' : '' ?>" href="index.php?page=account">Account</a>
                            </li>
                            <!-- Add Admin Settings Link -->
                            <li class="nav-item">
                                <a class="nav-link <?= $page === 'admin' ? 'active' : '' ?>" href="index.php?page=admin&action=settings">
                                    <i class="fas fa-cog me-1"></i> Admin
                                </a>
                            </li>
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=logout">
                                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                                </a>
                            </li>
                        </ul>
                    <?php else: ?>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link <?= $page === 'login' ? 'active' : '' ?>" href="index.php?page=login">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $page === 'register' ? 'active' : '' ?>" href="index.php?page=register">Register</a>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <main class="container py-4">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>