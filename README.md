# RTB-Banner-Bidder Documentation

## Overview

The `RTBBannerBidder` script is a PHP-based solution for processing Real-Time Bidding (RTB) requests and returning
banner campaign responses. It parses incoming bid requests, filters available campaigns to find the most suitable
option, and generates a JSON response with campaign details. The response meets RTB standards, ensuring that parameters
such as bid floor, device type, and geographical targeting are respected.

## Functionality

The `RTBBannerBidder` script includes the following core functionalities:

### Bid Request Parsing:

- Accepts a bid request in JSON format, which includes details like bid floor, device OS, geo-location, and banner
  dimensions.
- Validates the presence of required fields (`bidfloor`, `device.os`, `device.geo.country`) and extracts relevant data.

### Campaign Selection:

- Parses available campaigns and filters based on bid request parameters such as bid floor, device compatibility, and
  geo-location.
- Sorts eligible campaigns by bid price in descending order and selects the highest-priced campaign that meets the bid
  request requirements.

### Banner Campaign Response Generation:

- Generates a JSON response for the selected campaign with details such as campaign name, advertiser, bid price, ad ID,
  creative ID, image URL, and landing page URL.

### Error Handling:

- Provides error handling to manage cases of invalid JSON, missing required fields, or lack of suitable campaigns.
  Errors are logged for debugging.

## Usage Instructions

### Initialize Bid Request and Campaign JSON:

Define JSON strings for both the bid request and available campaigns, as shown in the examples below. The example bid
request JSON should include device OS, geo-location (country), and a bid floor.

```php
// Example bid request JSON
$bidRequestJson = '{
    "id": "uniqueBidRequest123",
    "imp": [
        {
            "id": "1",
            "banner": {
                "w": 320,
                "h": 50,
                "pos": 1,
                "format": [{"w": 320, "h": 50}]
            },
            "bidfloor": 0.05
        }
    ],
    "device": {
        "os": "android",
        "geo": {
            "country": "BGD"
        }
    }
}';

// Example campaign JSON
$campaignsJson = '[
    {
        "campaignname": "Bangladesh_Android_Campaign",
        "advertiser": "TopAd",
        "code": "AD12345",
        "creative_type": "1",
        "creative_id": 567890,
        "price": 0.1,
        "hs_os": "android",
        "country": "BGD",
        "image_url": "https://topadnetwork.com/images/ad_320x50.png",
        "url": "https://topadnetwork.com/click"
    }
]';
```

### Create and Run the RTBBannerBidder:

1. **Initialize an RTBBannerBidder Object**: Use the bid request and campaign JSON strings to create an
   `RTBBannerBidder` instance.
2. **Call handleBidRequest()**: Process the bid and retrieve either the selected campaign response or an error message.

```php
try {
    $rtbBannerBidder = new RTBBannerBidder($bidRequestJson, $campaignsJson);
    $response = $rtbBannerBidder->handleBidRequest();
    echo $response; // Outputs the JSON response or error
} catch (InvalidArgumentException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
```

### Test and Verify Output:

Ensure the output matches the expected JSON response format, which includes all necessary campaign details as specified
in the RTB standards.
