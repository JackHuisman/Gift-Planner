<?php
/**
 * Contact Page
 * 
 * Uses mailto links to open the user's email client
 */

$pageTitle = 'Contact Us';

// Process form submission to build mailto link
$mailtoLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Build message body with submitted info
    $messageBody = "Name: $name\n";
    $messageBody .= "Email: $email\n\n";
    $messageBody .= "Message:\n$message";
    
    // Create mailto link
    $to = 'support@giftplanner.com'; // Replace with your email
    $mailtoLink = 'mailto:' . urlencode($to) . 
                  '?subject=' . urlencode("Contact Form: $subject") . 
                  '&body=' . urlencode($messageBody);
    
    // Redirect to the mailto link
    echo '<script>window.location.href = "' . $mailtoLink . '";</script>';
    exit;
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h1 class="h3 mb-0">Contact Us</h1>
            </div>
            <div class="card-body">
                <p class="mb-4">Have questions, suggestions, or need assistance? Use one of the methods below to get in touch with our team.</p>
                
                <div class="alert alert-info mb-4">
                    <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i> How it works:</h5>
                    <p>When you submit this form, it will open your default email application with a pre-filled message. You can review and send the email from there.</p>
                </div>
                
                <form action="index.php?page=contact" method="post" id="contactForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= isset($_POST['name']) ? h($_POST['name']) : (isset($_SESSION['username']) ? h($_SESSION['username']) : '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= isset($_POST['email']) ? h($_POST['email']) : '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select class="form-select" id="subject" name="subject" required>
                            <option value="">Select a subject...</option>
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Feature Request">Feature Request</option>
                            <option value="Bug Report">Bug Report</option>
                            <option value="Account Issues">Account Issues</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Open Email Client
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0">Direct Contact Methods</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <h5><i class="fas fa-envelope text-primary me-2"></i> Email Us Directly</h5>
                        <p>For general inquiries: <a href="mailto:support@giftplanner.com">support@giftplanner.com</a></p>
                        <p>For business opportunities: <a href="mailto:business@giftplanner.com">business@giftplanner.com</a></p>
                    </div>
                    
                    <div class="col-md-6">
                        <h5><i class="fas fa-question-circle text-primary me-2"></i> Help Center</h5>
                        <p>Check our <a href="#">FAQ section</a> for quick answers to common questions.</p>
                        <p>Browse our <a href="#">Knowledge Base</a> for tutorials and guides.</p>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <h5 class="mb-3">Follow Us</h5>
                    <a href="#" class="btn btn-outline-primary me-2" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="btn btn-outline-info me-2" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="btn btn-outline-danger me-2" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="btn btn-outline-dark" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Alternative approach using JavaScript to generate mailto link
    const contactForm = document.getElementById('contactForm');
    
    contactForm.addEventListener('submit', function(e) {
        // Prevent standard form submission
        e.preventDefault();
        
        // Get form values
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;
        
        // Build message body
        let messageBody = `Name: ${name}\n`;
        messageBody += `Email: ${email}\n\n`;
        messageBody += `Message:\n${message}`;
        
        // Build mailto link
        const to = 'support@giftplanner.com';
        const mailtoLink = `mailto:${encodeURIComponent(to)}?subject=${encodeURIComponent('Contact Form: ' + subject)}&body=${encodeURIComponent(messageBody)}`;
        
        // Open email client
        window.location.href = mailtoLink;
    });
});
</script>