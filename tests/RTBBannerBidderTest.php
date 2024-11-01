<?php

use PHPUnit\Framework\TestCase;

// Include the RTBBannerBidder class file
require_once __DIR__ . '/../RTBBannerBidder.php';

class RTBBannerBidderTest extends TestCase
{
    /**
     * Test method for parsing a bid request to check if required fields are correctly extracted
     *
     * @return void
     * @throws Exception
     */
    public function testParseBidRequest()
    {
        // Sample bid request JSON string to test the parseBidRequest method
        $bidRequestJson = '{
            "id": "myB92gUhMdC5DUxndq3yAg",
            "imp": [
                {
                    "id": "1",
                    "banner": {
                        "w": 320,
                        "h": 50,
                        "pos": 1,
                        "format": [{"w": 776, "h": 393}]
                    },
                    "bidfloor": 0.01
                }
            ],
            "device": {
                "os": "android",
                "geo": {
                    "country": "BGD"
                }
            }
        }';

        // Initialize the RTBBannerBidder with the sample bid request and an empty campaigns JSON array
        $bidder = new RTBBannerBidder($bidRequestJson, '[]');

        // Call the parseBidRequest method to get the parsed data from the bid request
        $parsedRequest = $bidder->parseBidRequest();

        // Assert that the parsed request contains a 'bidfloor' key
        $this->assertArrayHasKey('bidfloor', $parsedRequest);

        // Assert that the parsed request contains a 'device_os' key
        $this->assertArrayHasKey('device_os', $parsedRequest);

        // Assert that the parsed request contains a 'country' key
        $this->assertArrayHasKey('country', $parsedRequest);
    }

    /**
     * Test method for selecting the best campaign based on parsed bid request criteria
     *
     * @throws Exception
     */
    public function testSelectBestCampaign()
    {
        // Create a sample parsed bid request array with bidfloor, device_os, and country values
        $parsedBidRequest = [
            'bidfloor' => 0.05,           // Minimum acceptable bid price
            'device_os' => 'android',     // Target device operating system
            'country' => 'BGD',           // Target country
        ];

        // Sample JSON array of campaigns to test campaign selection
        $campaignsJson = '[
            {
                "campaignname": "Test Campaign",
                "price": 0.1,            // Campaign bid price
                "hs_os": "android",      // Supported OS for the campaign
                "country": "BGD"         // Target country for the campaign
            }
        ]';

        // Initialize the RTBBannerBidder with an encoded version of the parsed bid request and campaigns JSON
        $bidder = new RTBBannerBidder(json_encode($parsedBidRequest), $campaignsJson);

        // Call the selectBestCampaign method to select the best matching campaign
        $selectedCampaign = $bidder->selectBestCampaign($parsedBidRequest);

        // Assert that a campaign was selected (not null)
        $this->assertNotNull($selectedCampaign);

        // Assert that the selected campaign's price meets or exceeds the bid floor
        $this->assertGreaterThanOrEqual($parsedBidRequest['bidfloor'], $selectedCampaign['price']);
    }
}
