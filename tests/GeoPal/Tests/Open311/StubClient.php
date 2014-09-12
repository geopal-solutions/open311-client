<?php

namespace GeoPal\Tests\Open311;

use GeoPal\Open311\BaseClient;
use GeoPal\Open311\Interfaces\IOpen311Client;

/**
 * Stub class to allow testing of BaseClient class
 *
 * Abstract classes cannot be tested directly,
 * this stub class is the closest we can get.
 */
class StubClient extends BaseClient implements IOpen311Client
{
    protected $endPoint = 'https://secure.toronto.ca/open311test/ws';
    protected $jurisdictionId = 'toronto.ca';
    protected $apiKey = null;
    protected $serviceRequestRequiredFields = null;

    /**
     * Helper function for testing
     * Allows setting the API key after instantiation
     *
     * @param string|null $apiKey
     */
    public function setApiKey($apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Helper function for testing
     * Allows setting the required fields for service requests after instantiation
     *
     * @param array $data
     */
    public function setServiceRequestRequiredFields($data)
    {
        $this->serviceRequestRequiredFields = $data;
    }

    public function createServiceRequest(
        $serviceCode,
        $lat,
        $lng,
        $addressString,
        $addressId = '',
        $email = '',
        $deviceId = '',
        $accountId = '',
        $firstName = '',
        $lastName = '',
        $phone = '',
        $description = '',
        $mediaUrl = '',
        $format = ''
    ) {
        // method implementation not required for related tests
    }

    public function getValidServiceCodes()
    {
        // method implementation not required for related tests
    }
}