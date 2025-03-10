<?php
/**
 * Email Configuration
 * 
 * This file contains email settings for sending gift suggestions and notifications
 * to users of the Gift Planner application.
 */

// Email settings
$emailConfig = [
    'from_email'    => 'notifications@yourgiftplanner.com',  // From email address
    'from_name'     => 'Gift Planner',                       // From name
    'reply_to'      => 'no-reply@yourgiftplanner.com',       // Reply-to address
    'smtp_enabled'  => false,                                // Whether to use SMTP
    'smtp_host'     => 'smtp.yourserver.com',                // SMTP host
    'smtp_port'     => 587,                                  // SMTP port
    'smtp_username' => 'your_smtp_username',                 // SMTP username
    'smtp_password' => 'your_smtp_password',                 // SMTP password
    'smtp_secure'   => 'tls',                                // SMTP security (tls/ssl)
    'template_path' => '../views/emails/'                    // Path to email templates
];

/**
 * Send an email using either PHP mail() or SMTP
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $plainText Plain text alternative
 * @return boolean Success status
 */
function sendEmail($to, $subject, $body, $plainText = '') {
    global $emailConfig;
    
    // If no plain text version was provided, create one by stripping HTML
    if (empty($plainText)) {
        $plainText = strip_tags($body);
    }
    
    // Use SMTP if enabled
    if ($emailConfig['smtp_enabled']) {
        return sendEmailSmtp($to, $subject, $body, $plainText);
    }
    
    // Otherwise use PHP mail() function
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'Content-Transfer-Encoding: 8bit',
        'From: ' . $emailConfig['from_name'] . ' <' . $emailConfig['from_email'] . '>',
        'Reply-To: ' . $emailConfig['reply_to'],
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Create a boundary for multipart email
    $boundary = md5(time());
    
    $message = "--{$boundary}\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $plainText . "\r\n\r\n";
    
    $message .= "--{$boundary}\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $body . "\r\n\r\n";
    
    $message .= "--{$boundary}--";
    
    // Set the content type header
    $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
    
    // Send the email
    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Send email via SMTP
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $plainText Plain text alternative
 * @return boolean Success status
 */
function sendEmailSmtp($to, $subject, $body, $plainText) {
    global $emailConfig;
    
    // For SMTP, we'll need a proper SMTP library
    // In a production environment, you'd use PHPMailer or similar
    // This is a placeholder for that implementation
    
    // Example code if using PHPMailer (you would need to include the library)
    /*
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $emailConfig['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $emailConfig['smtp_username'];
        $mail->Password   = $emailConfig['smtp_password'];
        $mail->SMTPSecure = $emailConfig['smtp_secure'];
        $mail->Port       = $emailConfig['smtp_port'];
        
        // Recipients
        $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
        $mail->addAddress($to);
        $mail->addReplyTo($emailConfig['reply_to']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $plainText;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
    */
    
    return false; // Replace with actual implementation
}

/**
 * Load and render an email template with variables
 * 
 * @param string $template Template filename
 * @param array $vars Variables to insert into template
 * @return string Rendered template
 */
function renderEmailTemplate($template, $vars = []) {
    global $emailConfig;
    
    $templateFile = $emailConfig['template_path'] . $template . '.php';
    
    if (!file_exists($templateFile)) {
        error_log("Email template not found: {$templateFile}");
        return '';
    }
    
    // Extract variables to make them accessible in the template
    extract($vars);
    
    // Capture output instead of displaying it
    ob_start();
    include $templateFile;
    $output = ob_get_clean();
    
    return $output;
}