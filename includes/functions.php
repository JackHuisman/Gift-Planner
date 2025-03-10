<?php
/**
 * Helper Functions
 * 
 * Common utility functions used throughout the application
 */

/**
 * Sanitize output to prevent XSS
 * 
 * @param string $str String to sanitize
 * @return string Sanitized string
 */
function h($str) {
    $str = $str ?? " ";
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 * 
 * @param string $date Date in Y-m-d format
 * @param string $format Output format (default: readable)
 * @return string Formatted date
 */
function formatDate($date, $format = 'readable') {
    if (empty($date)) {
        return '';
    }
    
    $timestamp = strtotime($date);
    
    switch ($format) {
        case 'readable':
            return date('F j, Y', $timestamp);
        case 'short':
            return date('M j, Y', $timestamp);
        case 'input':
            return date('Y-m-d', $timestamp);
        default:
            return date($format, $timestamp);
    }
}

/**
 * Format currency for display
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency code (default: USD)
 * @return string Formatted currency
 */
function formatCurrency($amount, $currency = 'USD') {
    switch ($currency) {
        case 'USD':
            return '$' . number_format($amount, 2);
        case 'EUR':
            return '€' . number_format($amount, 2);
        case 'GBP':
            return '£' . number_format($amount, 2);
        default:
            return number_format($amount, 2) . ' ' . $currency;
    }
}

/**
 * Function to calculate current age based on stored age and last update time
 * Add this at the top of the recipients/index.php file or in your functions.php file
 */
function calculateCurrentAge($storedAge, $updatedAt) {
    if (empty($storedAge) || empty($updatedAt)) {
        return $storedAge;
    }
    
    $lastUpdateDate = new DateTime($updatedAt);
    $currentDate = new DateTime();
    $yearDiff = $currentDate->diff($lastUpdateDate)->y;
    
    return $storedAge + $yearDiff;
}

/**
 * Calculate days until a date
 * 
 * @param string $date Date in Y-m-d format
 * @param bool $isAnnual Whether the date repeats annually
 * @return int Number of days until the date
 */
function daysUntil($date, $isAnnual = true) {
    $today = new DateTime('today');
    $target = new DateTime($date);
    
    if ($isAnnual) {
        // Set target to this year
        $target->setDate($today->format('Y'), $target->format('m'), $target->format('d'));
        
        // If the date has already passed this year, use next year
        if ($target < $today) {
            $target->modify('+1 year');
        }
    }
    
    $diff = $today->diff($target);
//    $diff = $diff->format('%r%a');
    return $diff->days;
}

/**
 * Generate relative time description (e.g., "2 days ago", "in 3 weeks")
 * 
 * @param string $date Date in Y-m-d format
 * @return string Relative time description
 */
function relativeTime($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 0) {
        // Future date
        $diff = abs($diff);
        
        if ($diff < 86400) {
            return "later today";
        } elseif ($diff < 172800) {
            return "tomorrow";
        } elseif ($diff < 604800) {
            return "in " . floor($diff / 86400) . " days";
        } elseif ($diff < 2592000) {
            return "in " . floor($diff / 604800) . " weeks";
        } elseif ($diff < 31536000) {
            return "in " . floor($diff / 2592000) . " months";
        } else {
            return "in " . floor($diff / 31536000) . " years";
        }
    } else {
        // Past date
        if ($diff < 86400) {
            return "today";
        } elseif ($diff < 172800) {
            return "yesterday";
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . " days ago";
        } elseif ($diff < 2592000) {
            return floor($diff / 604800) . " weeks ago";
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . " months ago";
        } else {
            return floor($diff / 31536000) . " years ago";
        }
    }
}

/**
 * Get occasion status class based on days until
 * 
 * @param int $daysUntil Days until the occasion
 * @return string CSS class name
 */
//function getOccasionStatusClass($daysUntil) {
//    if ($daysUntil === 0) {
//        return 'bg-danger text-white';
//    } elseif ($daysUntil <= 7) {
//        return 'bg-warning';
//    } elseif ($daysUntil <= 30) {
//        return 'bg-info';
//    } else {
//        return 'bg-light';
//    }
//}
function getOccasionStatusClass($daysLeft) {
    if ($daysLeft < 0) {
        return 'bg-danger'; // Red for past due
    } elseif ($daysLeft <= 7) {
        return 'bg-warning'; // Yellow for soon (7 days or less)
    } else {
        return 'bg-success'; // Green for more than 7 days
    }
}

/**
 * Generate a random password
 * 
 * @param int $length Password length
 * @return string Random password
 */
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    return $password;
}

/**
 * Check if a string is a valid date
 * 
 * @param string $date Date string
 * @param string $format Expected format
 * @return bool Whether the date is valid
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Get age from birthdate
 * 
 * @param string $birthdate Birthdate in Y-m-d format
 * @return int Age in years
 */
function getAge($birthdate) {
    $birth = new DateTime($birthdate);
    $today = new DateTime('today');
    $age = $birth->diff($today)->y;
    
    return $age;
}

/**
 * Get the default date for a specific occasion type
 * 
 * @param string $occasionType The type of occasion
 * @param int $year The year to calculate for (defaults to current year)
 * @return string|null Date in Y-m-d format or null if no default exists
 */
//function getDefaultOccasionDate($occasionType, $year = null) {
//    if ($year === null) {
//        $year = date('Y');
//    }
//    
//    switch (strtolower($occasionType)) {
//        case 'christmas':
//            return $year . '-12-25';
//            
//        case 'valentine\'s day':
//        case 'valentines day':
//        case 'valentine':
//            return $year . '-02-14';
//            
//        case 'halloween':
//            return $year . '-10-31';
//            
//        case 'new year':
//        case 'new year\'s day':
//            return $year . '-01-01';
//            
//        case 'thanksgiving':
//            // 4th Thursday in November (US)
//            $nov_first = strtotime($year . '-11-01');
//            $first_thursday = strtotime('thursday', $nov_first);
//            $fourth_thursday = strtotime('+3 weeks', $first_thursday);
//            return date('Y-m-d', $fourth_thursday);
//            
//        case 'mother\'s day':
//        case 'mothers day':
//            // 2nd Sunday in May (US)
//            $may_first = strtotime($year . '-05-01');
//            $first_sunday = strtotime('sunday', $may_first);
//            $second_sunday = strtotime('+1 week', $first_sunday);
//            return date('Y-m-d', $second_sunday);
//            
//        case 'father\'s day':
//        case 'fathers day':
//            // 3rd Sunday in June (US)
//            $june_first = strtotime($year . '-06-01');
//            $first_sunday = strtotime('sunday', $june_first);
//            $third_sunday = strtotime('+2 weeks', $first_sunday);
//            return date('Y-m-d', $third_sunday);
//            
//        case 'easter':
//            // Easter calculation (complex algorithm)
//            $a = $year % 19;
//            $b = floor($year / 100);
//            $c = $year % 100;
//            $d = floor($b / 4);
//            $e = $b % 4;
//            $f = floor(($b + 8) / 25);
//            $g = floor(($b - $f + 1) / 3);
//            $h = (19 * $a + $b - $d - $g + 15) % 30;
//            $i = floor($c / 4);
//            $k = $c % 4;
//            $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
//            $m = floor(($a + 11 * $h + 22 * $l) / 451);
//            $month = floor(($h + $l - 7 * $m + 114) / 31);
//            $day = (($h + $l - 7 * $m + 114) % 31) + 1;
//            return sprintf('%04d-%02d-%02d', $year, $month, $day);
//            
//        default:
//            return null;
//    }
//    
//    // If the date has already passed this year and we're calculating for the current year,
//    // return the date for next year
//    if ($year == date('Y') && strtotime($date) < time()) {
//        return getDefaultOccasionDate($occasionType, $year + 1);
//    }
//}

/**
 * Get the default date for a specific occasion type
 * 
 * @param string $occasionType Type of occasion
 * @param int $year Year to calculate for (defaults to current year)
 * @return string Date in Y-m-d format
 */
//function getDefaultOccasionDate($occasionType, $year = null) {
//    if ($year === null) {
//        $year = date('Y');
//    }
//    
//    $date = null;
//    
//    switch ($occasionType) {
//        case 'Christmas':
//            $date = "$year-12-25"; // December 25
//            break;
//            
//        case 'Valentine\'s Day':
//            $date = "$year-02-14"; // February 14
//            break;
//            
//        case 'Halloween':
//            $date = "$year-10-31"; // October 31
//            break;
//            
//        case 'New Year':
//            $date = "$year-01-01"; // January 1
//            break;
//            
//        case 'Mother\'s Day':
//            // Second Sunday in May
//            $mayFirst = strtotime("$year-05-01");
//            $firstSunday = strtotime('Sunday', $mayFirst);
//            if (date('d', $firstSunday) == 1) {
//                // If the first day of May is a Sunday
//                $secondSunday = strtotime('+7 days', $firstSunday);
//            } else {
//                $secondSunday = strtotime('+1 week', $firstSunday);
//            }
//            $date = date('Y-m-d', $secondSunday);
//            break;
//            
//        case 'Father\'s Day':
//            // Third Sunday in June
//            $juneFirst = strtotime("$year-06-01");
//            $firstSunday = strtotime('Sunday', $juneFirst);
//            $thirdSunday = strtotime('+2 weeks', $firstSunday);
//            $date = date('Y-m-d', $thirdSunday);
//            break;
//            
//        case 'Thanksgiving':
//            // Fourth Thursday in November (USA)
//            $novFirst = strtotime("$year-11-01");
//            $firstThursday = strtotime('Thursday', $novFirst);
//            $fourthThursday = strtotime('+3 weeks', $firstThursday);
//            $date = date('Y-m-d', $fourthThursday);
//            break;
//            
//        case 'Easter':
//            // Calculate Easter Sunday using the built-in PHP function
//            $easterTimestamp = easter_date($year);
//            $date = date('Y-m-d', $easterTimestamp);
//            break;
//            
//        default:
//            // For other occasions, use the current date
//            $date = date('Y-m-d');
//            break;
//    }
//    
//    // If the date has already passed this year and we're calculating for the current year,
//    // return the date for next year
//    if ($year == date('Y') && strtotime($date) < time()) {
//        return getDefaultOccasionDate($occasionType, $year + 1);
//    }
//    
//    return $date;
//}

/**
 * Get the default date for a specific occasion type
 * 
 * @param string $occasionType Type of occasion
 * @param int $year Year to calculate for (defaults to current year)
 * @return string Date in Y-m-d format
 */
function getDefaultOccasionDate($occasionType, $year = null) {
    if ($year === null) {
        $year = date('Y');
    }
    
    $date = null;
    
    switch ($occasionType) {
        case 'Christmas':
            $date = "$year-12-25"; // December 25
            break;
            
        case 'Valentine\'s Day':
            $date = "$year-02-14"; // February 14
            break;
            
        case 'Halloween':
            $date = "$year-10-31"; // October 31
            break;
            
        case 'New Year':
            $date = "$year-01-01"; // January 1
            break;
            
        case 'Mother\'s Day':
            // Second Sunday in May
            $mayFirst = strtotime("$year-05-01");
            $firstSunday = strtotime('Sunday', $mayFirst);
            if (date('d', $firstSunday) == 1) {
                // If the first day of May is a Sunday
                $secondSunday = strtotime('+7 days', $firstSunday);
            } else {
                $secondSunday = strtotime('+1 week', $firstSunday);
            }
            $date = date('Y-m-d', $secondSunday);
            break;
            
        case 'Father\'s Day':
            // Third Sunday in June
            $juneFirst = strtotime("$year-06-01");
            $firstSunday = strtotime('Sunday', $juneFirst);
            $thirdSunday = strtotime('+2 weeks', $firstSunday);
            $date = date('Y-m-d', $thirdSunday);
            break;
            
        case 'Thanksgiving':
            // Fourth Thursday in November (USA)
            $novFirst = strtotime("$year-11-01");
            $firstThursday = strtotime('Thursday', $novFirst);
            $fourthThursday = strtotime('+3 weeks', $firstThursday);
            $date = date('Y-m-d', $fourthThursday);
            break;
            
        case 'Easter':
            // Try to calculate Easter Sunday using the PHP function if available
            if (function_exists('easter_date')) {
                $easterTimestamp = easter_date($year);
                $date = date('Y-m-d', $easterTimestamp);
            } else {
                // Fallback: Easter is usually in April, set to April 15 as approximation
                $date = "$year-04-15";
            }
            break;
            
        default:
            // For other occasions, use the current date
            $date = date('Y-m-d');
            break;
    }
    
    // If the date has already passed this year and we're calculating for the current year,
    // return the date for next year
    if ($year == date('Y') && strtotime($date) < time()) {
        return getDefaultOccasionDate($occasionType, $year + 1);
    }
    
    return $date;
}