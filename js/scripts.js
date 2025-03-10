/**
 * Gift Planner - JavaScript Functions
 * 
 * This file contains client-side functionality for the Gift Planner application
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Password strength meter
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('confirm_password');
    var passwordStrengthMeter = document.getElementById('password-strength');
    
    if (passwordInput && passwordStrengthMeter) {
        passwordInput.addEventListener('input', function() {
            var password = passwordInput.value;
            var strength = 0;
            
            // Check password length
            if (password.length >= 8) {
                strength += 1;
            }
            
            // Check for mixed case
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
                strength += 1;
            }
            
            // Check for numbers
            if (password.match(/\d/)) {
                strength += 1;
            }
            
            // Check for special characters
            if (password.match(/[^a-zA-Z\d]/)) {
                strength += 1;
            }
            
            // Update the strength meter
            passwordStrengthMeter.className = 'progress-bar';
            if (strength === 0) {
                passwordStrengthMeter.style.width = '0%';
                passwordStrengthMeter.classList.add('bg-danger');
            } else if (strength === 1) {
                passwordStrengthMeter.style.width = '25%';
                passwordStrengthMeter.classList.add('bg-danger');
            } else if (strength === 2) {
                passwordStrengthMeter.style.width = '50%';
                passwordStrengthMeter.classList.add('bg-warning');
            } else if (strength === 3) {
                passwordStrengthMeter.style.width = '75%';
                passwordStrengthMeter.classList.add('bg-info');
            } else if (strength === 4) {
                passwordStrengthMeter.style.width = '100%';
                passwordStrengthMeter.classList.add('bg-success');
            }
        });
    }
    
    // Password confirmation validation
    if (passwordInput && confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (passwordInput.value === confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('');
            } else {
                confirmPasswordInput.setCustomValidity('Passwords must match');
            }
        });
    }
    
    // Date range validation
    var occasionDateInput = document.getElementById('occasion_date');
    var isAnnualCheckbox = document.getElementById('is_annual');
    
    if (occasionDateInput && isAnnualCheckbox) {
        // For non-annual events, validate that the date is in the future
        occasionDateInput.addEventListener('change', function() {
            if (!isAnnualCheckbox.checked) {
                var selectedDate = new Date(occasionDateInput.value);
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    occasionDateInput.setCustomValidity('For non-annual events, date must be in the future');
                } else {
                    occasionDateInput.setCustomValidity('');
                }
            } else {
                occasionDateInput.setCustomValidity('');
            }
        });
        
        isAnnualCheckbox.addEventListener('change', function() {
            // Trigger validation when checkbox status changes
            occasionDateInput.dispatchEvent(new Event('change'));
        });
    }
    
    // Budget range slider
    var priceMinInput = document.getElementById('price_min');
    var priceMaxInput = document.getElementById('price_max');
    var priceRangeOutput = document.getElementById('price_range_output');
    
    if (priceMinInput && priceMaxInput && priceRangeOutput) {
        function updatePriceOutput() {
            var min = parseFloat(priceMinInput.value);
            var max = parseFloat(priceMaxInput.value);
            
            // Ensure min doesn't exceed max
            if (min > max) {
                priceMaxInput.value = min;
                max = min;
            }
            
            // Format as currency
            var formattedMin = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(min);
            var formattedMax = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(max);
            
            priceRangeOutput.textContent = formattedMin + ' - ' + formattedMax;
        }
        
        priceMinInput.addEventListener('input', updatePriceOutput);
        priceMaxInput.addEventListener('input', updatePriceOutput);
        
        // Initial update
        updatePriceOutput();
    }
    
    // Gift selection handling
    var giftSelectionButtons = document.querySelectorAll('.gift-select-btn');
    
    giftSelectionButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            var suggestionId = this.getAttribute('data-suggestion-id');
            var occasionId = this.getAttribute('data-occasion-id');
            
            // Set the form values
            document.getElementById('suggestion_id').value = suggestionId;
            document.getElementById('occasion_id').value = occasionId;
            
            // Submit the form
            document.getElementById('gift-selection-form').submit();
        });
    });
    
    // Delete confirmation
    var deleteConfirmButtons = document.querySelectorAll('[data-delete-confirm]');
    
    deleteConfirmButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});