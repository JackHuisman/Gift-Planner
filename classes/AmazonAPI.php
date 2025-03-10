<?php
// AmazonAPI.php
class AmazonAPI {
    private $access_key;
    private $secret_key;
    private $partner_tag;
    private $region;
    
    public function __construct($config) {
        $this->access_key = $config['access_key'];
        $this->secret_key = $config['secret_key'];
        $this->partner_tag = $config['partner_tag'];
        $this->region = $config['region'];
    }
    
    /**
     * Generates a signed URL for Amazon Product Advertising API
     * 
     * @param array $params Operation parameters
     * @return string The signed request URL
     */
    public function getSignedRequest($params = []) {
        // Set required parameters
        $params = array_merge([
            'AccessKey' => $this->access_key,
            'PartnerTag' => $this->partner_tag,
            'PartnerType' => 'Associates',
            'Timestamp' => gmdate('Y-m-dTH:i:sZ'),
            'Service' => 'ProductAdvertisingAPI',
            'Operation' => 'SearchItems'
        ], $params);
        
        // Sort parameters alphabetically by key
        ksort($params);
        
        // Create the canonical request
        $canonicalQuery = http_build_query($params);
        $endpoint = "https://webservices.amazon.{$this->region}/paapi5/searchitems";
        $requestMethod = 'POST';
        
        // Create the string to sign
        $stringToSign = "{$requestMethod}\n{$this->region}.webservices.amazon.com\n/paapi5/searchitems\n{$canonicalQuery}";
        
        // Calculate the signature
        $signature = hash_hmac('sha256', $stringToSign, $this->secret_key, true);
        $signature = base64_encode($signature);
        
        // Add the signature to the request
        $params['Signature'] = $signature;
        
        return $endpoint . '?' . http_build_query($params);
    }
    
    public function searchGiftSuggestions($keywords, $minPrice, $maxPrice, $count = 5) {
        // Implement Amazon Product Advertising API call
        // This is a simplified version - you'll need to implement the full API signature process
        
        $params = [
            'Operation' => 'SearchItems',
            'Keywords' => $keywords,
            'MinPrice' => round($minPrice * 100),  // Amazon API uses cents
            'MaxPrice' => round($maxPrice * 100),
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
        
        $response = $this->makeRequest('SearchItems', $params);
        
        // Process response and return formatted gift suggestions
        $suggestions = [];
        
        if (isset($response['SearchResult']['Items'])) {
            foreach ($response['SearchResult']['Items'] as $item) {
                $suggestions[] = [
                    'product_title' => $item['ItemInfo']['Title']['DisplayValue'],
                    'product_description' => isset($item['ItemInfo']['Features']['DisplayValues']) ? 
                        implode("\n", $item['ItemInfo']['Features']['DisplayValues']) : '',
                    'amazon_url' => $item['DetailPageURL'],
                    'amazon_asin' => $item['ASIN'],
                    'price' => $item['Offers']['Listings'][0]['Price']['Amount'],
                    'image_url' => $item['Images']['Primary']['Medium']['URL']
                ];
            }
        }
        
        return $suggestions;
    }
    
private function makeRequest($operation, $params) {
    // Replace this line:
    $url = getSignedAmazonRequest($params);
    
    // With this implementation that doesn't rely on the global function:
    // Set required parameters
    $params = array_merge([
        'AccessKey' => $this->access_key,
        'PartnerTag' => $this->partner_tag,
        'PartnerType' => 'Associates',
        'Timestamp' => gmdate('Y-m-dTH:i:sZ'),
        'Service' => 'ProductAdvertisingAPI',
        'Operation' => $operation
    ], $params);
    
    // Sort parameters alphabetically by key
    ksort($params);
    
    // Create the canonical request
    $canonicalQuery = http_build_query($params);
    $endpoint = "https://webservices.amazon.{$this->region}/paapi5/searchitems";
    $requestMethod = 'POST';
    
    // Create the string to sign
    $stringToSign = "{$requestMethod}\nwebservices.amazon.{$this->region}\n/paapi5/searchitems\n{$canonicalQuery}";
    
    // Calculate the signature
    $signature = hash_hmac('sha256', $stringToSign, $this->secret_key, true);
    $signature = base64_encode($signature);
    
    // Add the signature to the request
    $params['Signature'] = $signature;
    
    $url = $endpoint . '?' . http_build_query($params);
    
    // Implementation details for Amazon Product Advertising API v5
    // You'd need to generate proper authentication headers
    
    // Return mock data for demonstration
    return [
        'SearchResult' => [
            'Items' => [
                // Sample items would be returned here
            ]
        ]
    ];
}
    
    /**
     * Create an Amazon affiliate link with your associate ID
     * 
     * @param string $asin Amazon product ASIN
     * @return string Affiliate link
     */
    public function createAffiliateLink($asin) {
        $domain = "amazon.{$this->region}";
        
        return "https://{$domain}/dp/{$asin}?tag={$this->partner_tag}";
    }
}
?>