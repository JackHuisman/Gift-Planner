<?php
/**
 * Dashboard page
 * Main user interface after login
 */

$pageTitle = 'Dashboard';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-3">Welcome, <?= h($_SESSION['username']) ?></h1>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="index.php?page=recipients&action=add" class="btn btn-primary">
            <i class="fas fa-user-plus me-1"></i> Add Recipient
        </a>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Recipients</h6>
                        <h2 class="mb-0">
                            <?php
                            $recipient = new Recipient($db);
                            $recipients = $recipient->getByUserId($_SESSION['user_id']);
                            echo count($recipients);
                            ?>
                        </h2>
                    </div>
                    <div class="fs-1 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-2">
                <a href="index.php?page=recipients" class="text-decoration-none">View all recipients</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Upcoming Occasions</h6>
                        <h2 class="mb-0">
                            <?= count($upcomingOccasions) ?>
                        </h2>
                    </div>
                    <div class="fs-1 text-success">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-2">
                <span class="text-muted">In the next 30 days</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Gift Suggestions</h6>
                        <h2 class="mb-0">
                            <?php
                            $giftSuggestion = new GiftSuggestion($db);
                            $suggestionsCount = $giftSuggestion->countForUser($_SESSION['user_id']);
                            echo $suggestionsCount;
                            ?>
                        </h2>
                    </div>
                    <div class="fs-1 text-info">
                        <i class="fas fa-gift"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-2">
                <!--<a href="#" class="text-decoration-none">View all suggestions</a>-->
                <!--<a href="index.php?page=suggestions" class="view-all">View all suggestions</a>-->
<a href="index.php?page=suggestions&action=all" class="view-all">View all suggestions</a>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Occasions -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Upcoming Occasions</h5>
    </div>
    <div class="card-body p-0">
        <?php
        
// Get upcoming occasions
$occasion = new Occasion($db);
$upcomingOccasions = $occasion->getUpcomingForUser($_SESSION['user_id'], 30); // Next 30 days

// Debug code - you can temporarily add this to check what's happening
echo "<!-- Debug: Found " . count($upcomingOccasions) . " upcoming occasions -->";

// If there are no upcoming occasions, you can provide a default message
        
        
        if (empty($upcomingOccasions)): ?>
            <div class="p-4 text-center">
                <p class="text-muted mb-0">No upcoming occasions in the next 30 days.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Occasion</th>
                            <th>Date</th>
                            <th>Days Left</th>
                            <th>Budget</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingOccasions as $occasion): ?>
                            <?php 
                            $daysUntil = daysUntil($occasion['occasion_date'], $occasion['is_annual']);
                            $statusClass = getOccasionStatusClass($daysUntil);
                            ?>
                            <tr>
                                <td>
                                       <a href="index.php?page=recipients&action=view&id=<?= $occasion['recipient_id'] ?>">
                                        <?= h($occasion['recipient_name']) ?>
                                    </a>
                                </td>
                                <td><?= h($occasion['occasion_type']) ?></td>
                                <td><?= formatDate($occasion['occasion_date']) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?> rounded-pill">
                                        <?= $daysUntil ?> day<?= $daysUntil !== 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <?= formatCurrency($occasion['price_min']) ?> - <?= formatCurrency($occasion['price_max']) ?>
                                </td>
                                <td>
                                    <a href="index.php?page=occasions&action=view&id=<?= $occasion['occasion_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary me-1" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="index.php?page=suggestions&occasion_id=<?= $occasion['occasion_id'] ?>" 
                                       class="btn btn-sm btn-outline-success" title="View Gift Suggestions">
                                        <i class="fas fa-gift"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-light text-center">
        <a href="index.php?page=recipients" class="text-decoration-none">Manage all recipients and occasions</a>
    </div>
</div>

<!-- Recent Activity -->                                        
<?php
// Get recent activity
$activityLog = new ActivityLog($db);
$activities = $activityLog->getRecentForUser($_SESSION['user_id']);
?>

<!-- Recent Activity -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Recent Activity</h5>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            <?php if (empty($activities)): ?>
                <li class="list-group-item text-center py-4">
                    <p class="text-muted mb-0">No recent activity. Start by adding recipients and occasions!</p>
                </li>
            <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                    <li class="list-group-item">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="avatar bg-<?= $activity['color'] ?>-light text-<?= $activity['color'] ?> rounded-circle p-2">
                                    <i class="<?= $activity['icon'] ?>"></i>
                                </div>
                            </div>
                            <div>
                                <p class="mb-1"><?= h($activity['text']) ?></p>
                                <?php if (isset($activity['details'])): ?>
                                    <p class="small text-muted mb-0">
                                        <?= h($activity['details']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="small text-muted mb-0">
                                    <?= relativeTime($activity['created_at']) ?>
                                </p>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
    <?php if (count($activities) > 0): ?>
    <div class="card-footer bg-light text-center">
        <!--<a href="index.php?page=account&action=activity" class="text-decoration-none">View all activity</a>-->
        <a href="index.php?page=activity&action=view-all" class="text-decoration-none">View all activity</a>
    </div>
    <?php endif; ?>
</div>

<!-- View All Activity Button 
<div class="row mt-4 mb-4">
    <div class="col-12 text-center">
        <a href="index.php?page=activity&action=view-all" class="btn btn-lg btn-outline-primary">
            <i class="fas fa-calendar me-1"></i> View All Activity
        </a>
    </div>
</div>-->