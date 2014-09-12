open311-client
==============

An Open311 GeoReport v2 client library for PHP 5.3+

# About

This client library for PHP 5.3+ allows you to query data from and submit data to all server systems implementing the [Open311 GeoReport V2](http://wiki.open311.org/GeoReport_v2) standard. 

Currently, a client library for the Toronto jurisdiction is the only client included. You can create your own client libraries by extending the `GeoPal\Open311\BaseClient` class. It is also strongly recommended that your own client classes also implement the `GeoPal\Open311\Interfaces\IOpen311Client` interface.

# Requirements

- PHP 5.3.0 or greater with cURL (duh!)
- [Composer](https://getcomposer.org) to install dependencies

# Setup

- Clone the repo or download zip file
- Run `php composer update` to install dependencies

# Usage

A usage example for the TorontoClient class can be found below. Please note that you have to obtain an API key in order to be able to submit Service Requests to their systems.

```php

use \GeoPal\Open311\Clients\TorontoClient;
use \GeoPal\Open311\ServiceRequest;
use \GeoPal\Open311\ServiceRequestResponse;

// Creating a client instance
$client = new TorontoClient('myApiKey');

// List available services
$servicesArray = $client->listServices();

// Get service metadata
$serviceDefinitionArray = $client->getServiceDefinition($serviceCode);

// Create service request
$response = $this->createServiceRequest(
    $serviceCode,
    $lat,
    $lng,
    $addressString,
    $addressId,
    $email,
    $deviceId,
    $accountId,
    $firstName,
    $lastName,
    $phone,
    $description,
    $mediaUrl
);

// Get service request details from response
$serviceRequestId = $response->get(ServiceRequestResponse::FIELD_SERVICE_REQUEST_ID);
$serviceRequestToken = $response->get(ServiceRequestResponse::FIELD_TOKEN);

// Get a list of service requests
$serviceRequestsArray = $client->getAllServiceRequests(
    $serviceRequestId,
    $serviceCode,
    $startDate,
    $endDate,
    $status
);

// Get service request id from a service request token
$serviceRequestId = $client->getServiceRequestIdFromToken($token);

// Getting service request data by service request id
$serviceRequest = $client->getServiceRequest($serviceRequestId);

```

There is also another method to submit a service request:

```php

$serviceRequest = ServiceRequest::fromArray($serviceRequestDataArray);
$response = $client->postServiceRequest($serviceRequest);

```

# Notes

- Currently, the only format to send data to and recieve data from Open311 is JSON. This does not affect results at all, however, XML format is planned to be added later on as a selectable option.
- Tests currently cover only about 50% of the code base
