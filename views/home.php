<?php
/**
 * Home page
 * Landing page for non-authenticated users
 */

$pageTitle = 'Welcome';
?>

<div class="row align-items-center min-vh-75">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <h1 class="display-4 fw-bold mb-4">Never Forget a Special Occasion Again</h1>
        <p class="lead mb-4">
            Gift Planner helps you organize and remember birthdays, anniversaries, and special occasions. 
            Get personalized gift suggestions based on each recipient's preferences and interests.
        </p>
        <div class="d-grid gap-2 d-md-flex">
            <a href="index.php?page=register" class="btn btn-primary btn-lg px-4 me-md-2">Get Started</a>
            <a href="index.php?page=login" class="btn btn-outline-secondary btn-lg px-4">Login</a>
        </div>
    </div>
    <div class="col-lg-6">
        <img src="images/gift-planner-hero.jpg" class="img-fluid rounded shadow" alt="Gift Planner Preview">
    </div>
</div>

<hr class="my-5">

<div class="row text-center">
    <div class="col-12">
        <h2 class="mb-5">How It Works</h2>
    </div>
    <div class="col-md-4 mb-4">
        <div class="p-4 bg-light rounded shadow-sm">
            <div class="fs-1 text-primary mb-3">
                <i class="fas fa-user-friends"></i>
            </div>
            <h3>Add Recipients</h3>
            <p>Create profiles for friends, family members, and colleagues with their preferences and interests.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="p-4 bg-light rounded shadow-sm">
            <div class="fs-1 text-primary mb-3">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3>Track Special Dates</h3>
            <p>Organize birthdays, anniversaries, and other celebrations in one easy-to-manage calendar.</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="p-4 bg-light rounded shadow-sm">
            <div class="fs-1 text-primary mb-3">
                <i class="fas fa-gift"></i>
            </div>
            <h3>Get Gift Ideas</h3>
            <p>Receive personalized gift suggestions based on each person's age, interests, and your budget.</p>
        </div>
    </div>
</div>

<hr class="my-5">

<div class="row align-items-center my-5">
    <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
        <h2>Timely Reminders</h2>
        <p class="lead">Never miss an important date again. Gift Planner sends email notifications two weeks before each occasion, giving you plenty of time to select the perfect gift.</p>
        <ul class="list-unstyled">
            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Email notifications</li>
            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Customizable reminder timing</li>
            <li><i class="fas fa-check text-success me-2"></i> Annual reminders for recurring events</li>
        </ul>
    </div>
    <div class="col-lg-6 order-lg-1">
        <img src="images/reminders.jpg" class="img-fluid rounded shadow" alt="Timely Reminders">
    </div>
</div>

<div class="row align-items-center my-5">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <h2>Personalized Gift Suggestions</h2>
        <p class="lead">Our intelligent recommendation system suggests gifts based on recipient attributes, occasion type, and your budget preferences.</p>
        <ul class="list-unstyled">
            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Age-appropriate ideas</li>
            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Budget-friendly options</li>
            <li><i class="fas fa-check text-success me-2"></i> Direct links to purchase</li>
        </ul>
    </div>
    <div class="col-lg-6">
        <img src="images/gift-suggestions.jpg" class="img-fluid rounded shadow" alt="Gift Suggestions">
    </div>
</div>

<hr class="my-5">

<div class="row text-center mb-5">
    <div class="col-12">
        <h2 class="mb-4">Ready to Get Started?</h2>
        <p class="lead mb-4">Join thousands of users who never miss important occasions.</p>
        <a href="index.php?page=register" class="btn btn-primary btn-lg">Create Your Account Now</a>
    </div>
</div>