<?php
// views/activity/view-all.php

// Instead of using relative paths, use the same include approach as your other files
//include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Use your existing database connection from wherever it's established in your application
// (Assuming it's available as $db variable by this point like in your other files)

// Create instances of necessary classes
$recipient = new Recipient($db);
$occasion = new Occasion($db);

// Get all active recipients for the current user
$recipients = $recipient->getByUserId($user_id);

// Your existing view-all.php code continues here...
?>

<div class="container mt-4">
    <h1>All Activities</h1>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h4 mb-0">Upcoming Occasions</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($recipients)): ?>
                        <p>No recipients found. <a href="add-recipient.php">Add a recipient</a> to get started.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Recipient</th>
                                        <th>Occasion</th>
                                        <th>Date</th>
                                        <th>Days Until</th>
                                        <th>Price Range</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $hasOccasions = false;
                                    foreach ($recipients as $r) {
                                        $recipientOccasions = $occasion->getByRecipientId($r['recipient_id']);
                                        
                                        foreach ($recipientOccasions as $o) {
                                            $hasOccasions = true;
                                            
                                            // Calculate days until
                                            $occasionDate = new DateTime($o['occasion_date']);
                                            $currentYear = (int) date('Y');
                                            
                                            // If it's an annual occasion, adjust the year
                                            if ($o['is_annual']) {
                                                $occasionMonth = (int) $occasionDate->format('m');
                                                $occasionDay = (int) $occasionDate->format('d');
                                                $currentMonth = (int) date('m');
                                                $currentDay = (int) date('d');
                                                
                                                // Set to current year
                                                $occasionDate->setDate($currentYear, $occasionMonth, $occasionDay);
                                                
                                                // If the date has already passed this year, set to next year
                                                if ($occasionMonth < $currentMonth || 
                                                    ($occasionMonth == $currentMonth && $occasionDay < $currentDay)) {
                                                    $occasionDate->setDate($currentYear + 1, $occasionMonth, $occasionDay);
                                                }
                                            }
                                            
                                            $today = new DateTime();
                                            $interval = $today->diff($occasionDate);
                                            $daysUntil = $interval->days;
                                            
                                            // Format price range
                                            $priceRange = '$' . number_format($o['price_min'], 2) . ' - $' . number_format($o['price_max'], 2);
                                            
                                            echo "<tr>";
                                            echo "<td>{$r['name']}</td>";
                                            echo "<td>{$o['occasion_type']}</td>";
                                            echo "<td>" . $occasionDate->format('M d, Y') . "</td>";
                                            echo "<td>{$daysUntil}</td>";
                                            echo "<td>{$priceRange}</td>";
                                            echo "<td>";
                                            echo "<a href='view-occasion.php?id={$o['occasion_id']}' class='btn btn-sm btn-primary'>View</a> ";
                                            echo "<a href='edit-occasion.php?id={$o['occasion_id']}' class='btn btn-sm btn-secondary'>Edit</a>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                    
                                    if (!$hasOccasions) {
                                        echo "<tr><td colspan='6'>No occasions found. Add occasions to your recipients.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php // include 'includes/footer.php'; ?>