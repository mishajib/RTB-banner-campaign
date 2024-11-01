<?php
#=========================================================================
# Author: MD. MAZHARUL ISLAM (SHAJIB)
# Implementation of Real-Time Bidding (RTB) Banner Campaign Response
#=========================================================================

class RTBBannerBidder
{
    // Define private properties to store the bid request and campaigns data
    private mixed $bidRequest;
    private mixed $campaigns;

    /**
     * Accepts bid request JSON and campaign JSON data as input
     * @param $bidRequestJson
     * @param $campaignsJson
     */
    public function __construct($bidRequestJson, $campaignsJson)
    {
        // Decode JSON data into associative arrays and store in properties
        $this->bidRequest = json_decode($bidRequestJson, true);
        $this->campaigns = json_decode($campaignsJson, true);

        // Check for JSON decoding errors and throw an exception if invalid
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON format for bid request or campaign data!");
        }
    }

    /**
     * Parse the bid request data and extract relevant parameters
     * @return array
     * @throws Exception
     */
    public function parseBidRequest(): array
    {
        // Check if required fields are present in the bid request
        if (!isset($this->bidRequest['imp'][0]['bidfloor'], $this->bidRequest['device']['os'], $this->bidRequest['device']['geo']['country'])) {
            throw new Exception("Required bid request fields are missing!");
        }

        // Extract relevant information from the bid request and return it as an array
        return [
            'bidfloor' => $this->bidRequest['imp'][0]['bidfloor'],  // Minimum bid floor
            'device_os' => strtolower($this->bidRequest['device']['os']),  // Device operating system
            'country' => $this->bidRequest['device']['geo']['country'],  // Geo-location country
            'banner_format' => $this->bidRequest['imp'][0]['banner']['format']  // Supported banner formats
        ];
    }

    /**
     * Select the best campaign based on the parsed bid request data
     *
     * @param $parsedBidRequest
     * @return mixed
     * @throws Exception
     */
    public function selectBestCampaign($parsedBidRequest): mixed
    {
        // Filter campaigns that meet bid floor, device compatibility, and geo-targeting requirements
        $eligibleCampaigns = array_filter($this->campaigns, function ($campaign) use ($parsedBidRequest) {
            return $campaign['price'] >= $parsedBidRequest['bidfloor'] &&  // Check if campaign price meets bid floor
                str_contains(strtolower($campaign['hs_os']), $parsedBidRequest['device_os']) &&  // Check device compatibility
                strtolower($campaign['country']) === strtolower($parsedBidRequest['country']);  // Check if country matches
        });

        // Throw exception if no campaign meets the criteria
        if (empty($eligibleCampaigns)) {
            throw new Exception("No suitable campaign found!");
        }

        // Sort eligible campaigns by price in descending order and select the highest-priced campaign
        usort($eligibleCampaigns, function ($a, $b) {
            return $b['price'] <=> $a['price'];
        });

        // Return the campaign with the highest price
        return $eligibleCampaigns[0];
    }

    /**
     * Generate a JSON response for the selected campaign
     *
     * @throws JsonException
     */
    private function generateBannerResponse($campaign): false|string
    {
        // Return a JSON-encoded response containing campaign details
        return json_encode([
            'id' => uniqid(),  // Unique identifier for the response
            'campaign_name' => $campaign['campaignname'] ?? null,  // Campaign name
            'advertiser' => $campaign['advertiser'] ?? null,  // Advertiser name
            'bid_price' => $campaign['price'] ?? null,  // Bid price offered
            'ad_id' => $campaign['code'] ?? null,  // Unique ad ID
            'creative_id' => $campaign['creative_id'] ?? null,  // Creative ID for the ad
            'image_url' => $campaign['image_url'] ?? null,  // URL to the ad's image
            'landing_page_url' => $campaign['url'] ?? null,  // URL to the landing page
            'creative_type' => $campaign['creative_type'] ?? null,  // Type of creative (e.g., banner, video)
        ], JSON_THROW_ON_ERROR);  // Throw exception on JSON encoding error
    }

    /**
     * Handle the bid request by parsing, selecting, and responding with the best campaign
     *
     * @return false|string
     */
    public function handleBidRequest(): false|string
    {
        try {
            // Parse the bid request to get relevant parameters
            $parsedBidRequest = $this->parseBidRequest();

            // Select the best campaign based on the parsed bid request
            $selectedCampaign = $this->selectBestCampaign($parsedBidRequest);

            // Generate a JSON response for the selected campaign
            return $this->generateBannerResponse($selectedCampaign);
        } catch (Exception $e) {
            // Log any exception that occurs for debugging purposes
            error_log($e->getMessage());

            // Return a JSON error response
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Example usage of RTBBannerBidder

// JSON data representing a sample bid request
$bidRequestJson = file_get_contents('bidrequestJson.json');

// JSON data representing a sample campaign
$campaignsJson = file_get_contents('campaignJson.json');


// Create a new RTBBannerBidder instance with bid request and campaign data
try {
    $rtbBannerBidder = new RTBBannerBidder($bidRequestJson, $campaignsJson);

    // Process the bid request and echo the response
    $response = $rtbBannerBidder->handleBidRequest();
    echo $response;
} catch (InvalidArgumentException $e) {
    // Handle invalid JSON format error and output error message
    echo json_encode(['error' => $e->getMessage()]);
}
