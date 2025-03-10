<?php
/**
 * Amazon Product Advertising API Configuration
 * 
 * This file contains the configuration settings for connecting to Amazon's
 * Product Advertising API to retrieve product information and generate affiliate links.
 */

// Amazon API credentials and settings
$amazonConfig = [
    'access_key'  => 'YOUR_ACCESS_KEY',      // Your Amazon PA-API Access Key
    'secret_key'  => 'YOUR_SECRET_KEY',      // Your Amazon PA-API Secret Key
    'partner_tag' => 'giftplanner09-20',    // Your Amazon Associates ID (e.g., yoursite-20)
    'region'      => 'com',                  // Amazon marketplace region (.com, .co.uk, etc.)
    'host'        => 'webservices.amazon.com', // API host
    'service'     => 'ProductAdvertisingAPI', // Service name
    'version'     => '20201001',             // PA-API version
    'cache_time'  => 3600                    // Cache API responses for 1 hour
];

// Only define these functions if they don't already exist
if (!function_exists('getSignedAmazonRequest')) {
/**
 * Generates a signed URL for Amazon Product Advertising API
 * 
 * @param array $params Operation parameters
 * @return string The signed request URL
 */
function getSignedAmazonRequest($params = []) {
    global $amazonConfig;
    
    // Set required parameters
    $params = array_merge([
        'AccessKey' => $amazonConfig['access_key'],
        'PartnerTag' => $amazonConfig['partner_tag'],
        'PartnerType' => 'Associates',
        'Timestamp' => gmdate('Y-m-dTH:i:sZ'),
        'Service' => $amazonConfig['service'],
        'Operation' => 'SearchItems'
    ], $params);
    
    // Sort parameters alphabetically by key
    ksort($params);
    
    // Create the canonical request
    $canonicalQuery = http_build_query($params);
    $endpoint = "https://{$amazonConfig['host']}/paapi5/searchitems";
    $requestMethod = 'POST';
    
    // Create the string to sign
    $stringToSign = "{$requestMethod}\n{$amazonConfig['host']}\n/paapi5/searchitems\n{$canonicalQuery}";
    
    // Calculate the signature
    $signature = hash_hmac('sha256', $stringToSign, $amazonConfig['secret_key'], true);
    $signature = base64_encode($signature);
    
    // Add the signature to the request
    $params['Signature'] = $signature;
    
    return $endpoint . '?' . http_build_query($params);
}
}

if (!function_exists('createAmazonAffiliateLink')) {
/**
 * Create an Amazon affiliate link with your associate ID
 * 
 * @param string $asin Amazon product ASIN
 * @return string Affiliate link
 */
function createAmazonAffiliateLink($asin) {
    global $amazonConfig;
    
    $domain = "amazon.{$amazonConfig['region']}";
    $tag = $amazonConfig['partner_tag'];
    
    return "https://{$domain}/dp/{$asin}?tag={$tag}";
}
}

/**
 * Search for gift suggestions based on recipient and occasion details
 * 
 * @param array $recipientData Recipient information
 * @param array $occasionData Occasion information
 * @param int $count Number of suggestions to return
 * @return array Gift suggestions
 */
function searchGiftSuggestions($recipientData, $occasionData, $count = 5) {
    // Build keywords based on occasion and recipient
    $keywords = 'gift';
    
    // Add age-specific terms if appropriate
    if ($occasionData['age_appropriate'] && $occasionData['occasion_type'] === 'Birthday') {
        // Add age category to search
        if (!empty($recipientData['age_category'])) {
            switch ($recipientData['age_category']) {
                case 'Child':
                    $keywords .= ' for kids';
                    
                    // Add specific age for children if birth year is available
                    if (!empty($recipientData['birth_year'])) {
                        $age = date('Y') - $recipientData['birth_year'];
                        $keywords .= ' ' . $age . ' year old';
                    } else {
                        $keywords .= ' children';
                    }
                    break;
                case 'Teen':
                    $keywords .= ' for teenagers';
                    break;
                case 'Young Adult':
                    $keywords .= ' for young adults';
                    break;
                case 'Adult':
                    $keywords .= ' for adults';
                    break;
                case 'Senior':
                    $keywords .= ' for seniors';
                    break;
            }
        }
    }
    
    // Add gender if available
    if (!empty($recipientData['gender'])) {
        if ($recipientData['gender'] === 'Male') {
            $keywords .= ' for men';
        } elseif ($recipientData['gender'] === 'Female') {
            $keywords .= ' for women';
        }
    }
    
    // Add occasion type
    $keywords .= ' for ' . strtolower($occasionData['occasion_type']);
    
    // Add relationship if available
    if (!empty($recipientData['relationship'])) {
        $keywords .= ' for ' . strtolower($recipientData['relationship']);
    }
    
    // Add notes keywords if available (extract key terms)
    if (!empty($occasionData['notes'])) {
        // Extract key terms from notes (simple approach)
        $notes = strtolower($occasionData['notes']);
        $keywords .= $this->extractKeyTerms($notes);
    }
    
    // Prepare API parameters
    $params = [
        'Operation' => 'SearchItems',
        'Keywords' => $keywords,
        'MinPrice' => round($occasionData['price_min'] * 100),  // Amazon API uses cents
        'MaxPrice' => round($occasionData['price_max'] * 100),
        'ItemCount' => $count,
        'Resources' => [
            'Images.Primary.Medium',
            'ItemInfo.Title',
            'ItemInfo.Features',
            'Offers.Listings.Price'
        ],
        'PartnerTag' => $this->partner_tag,
        'PartnerType' => 'Associates'
    ];
    
    // Make the API request
    $response = $this->makeRequest('SearchItems', $params);
    
    // Process and return the results
    return $this->processSearchResults($response);
}

/**
 * Extract key terms from notes text
 * 
 * @param string $text Text to extract terms from
 * @return string Key terms for search
 */
function extractKeyTerms($text) {
    // List of common interests and hobbies to look for
    $interests = [
        'reading', 'books', 'novels', 'fiction', 'literature',
        'music', 'guitar', 'piano', 'drums', 'violin', 'instruments',
        'sports', 'football', 'soccer', 'basketball', 'baseball', 'tennis',
        'cooking', 'baking', 'kitchen', 'recipes', 'chef',
        'gaming', 'video games', 'playstation', 'xbox', 'nintendo', 'pc games',
        'art', 'painting', 'drawing', 'sketching', 'crafts',
        'travel', 'hiking', 'outdoors', 'camping', 'adventure',
        'technology', 'gadgets', 'computers', 'electronics',
        'fashion', 'clothing', 'jewelry', 'accessories',
        'fitness', 'yoga', 'exercise', 'workout', 'gym',
        'gardening', 'plants', 'flowers', 'herbs',
        'photography', 'camera', 'photos',
        'movies', 'films', 'cinema', 'tv shows', 'series'
    ];
    
    $extractedTerms = '';
    
    foreach ($interests as $interest) {
        if (strpos($text, $interest) !== false) {
            $extractedTerms .= ' ' . $interest;
        }
    }
    
    return $extractedTerms;
}