<?php

namespace GeoPal\Open311\Clients;

use GeoPal\Open311\Exceptions\Open311Exception;
use Guzzle\Http\Client;
use GeoPal\Open311\BaseClient;
use GeoPal\Open311\Interfaces\IOpen311Client;
use GeoPal\Open311\ServiceRequest;
use GeoPal\Open311\ServiceRequestResponse;

/**
 * Class TorontoClient
 *
 * @author Gabor.Zelei@geopal-solutions.com
 * @package GeoPal\Open311\Toronto
 *
 * Open311 client class for the toronto.ca jurisdiction
 */
class TorontoClient extends BaseClient implements IOpen311Client
{
    const SERVICE_CODE_GRAFFITI_CITY_BRIDGE = 'CSROWBM-03';
    const SERVICE_CODE_GRAFFITI_CITY_LITTER_BIN = 'SWLMALB-02';
    const SERVICE_CODE_GRAFFITI_CITY_ROAD = 'CSROWC-05';
    const SERVICE_CODE_GRAFFITI_CITY_SIDEWALK = 'CSROSC-14';
    const SERVICE_CODE_POTHOLE = 'CSROWR-12';

    /**
     * @var string
     */
    protected $endPoint = 'https://secure.toronto.ca/webwizard/ws';

    /**
     * @var string
     */
    protected $jurisdictionId = 'toronto.ca';

    /**
     * @var string
     */
    protected $apiKey = null;

    /**
     * Fields each Service Request must contain value for
     *
     * @var array
     */
    protected $serviceRequestRequiredFields = array(
        ServiceRequest::FIELD_SERVICE_CODE,
        ServiceRequest::FIELD_ADDRESS_STRING,
        ServiceRequest::FIELD_LATITUDE,
        ServiceRequest::FIELD_LONGITUDE
    );

    /**
     * @param string $apiKey
     * @param Client $guzzleClient
     */
    public function __construct($apiKey = null, Client $guzzleClient = null)
    {
        $this->apiKey = $apiKey;
        parent::__construct($guzzleClient);
    }

    /**
     * Creates and posts a service request
     *
     * @param string $serviceCode
     * @param float $lat
     * @param float $lng
     * @param string $addressString
     * @param string $addressId
     * @param string $email
     * @param string $deviceId
     * @param string $accountId
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $description
     * @param string $mediaUrl
     * @param string $format
     * @return ServiceRequestResponse
     * @throws Open311Exception
     */
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
        $format = self::FORMAT_JSON
    ){
        $data = array(
            ServiceRequest::FIELD_SERVICE_CODE => $serviceCode,
            ServiceRequest::FIELD_LATITUDE => (!is_null($lat) && is_numeric($lat)) ? (float)$lat : null,
            ServiceRequest::FIELD_LONGITUDE => (!is_null($lng) && is_numeric($lng)) ? (float)$lng : null,
            ServiceRequest::FIELD_ADDRESS_STRING => $addressString,
            ServiceRequest::FIELD_ADDRESS_ID => $addressId,
            ServiceRequest::FIELD_EMAIL => $email,
            ServiceRequest::FIELD_DEVICE_ID => $deviceId,
            ServiceRequest::FIELD_ACCOUNT_ID => $accountId,
            ServiceRequest::FIELD_FIRST_NAME => $firstName,
            ServiceRequest::FIELD_LAST_NAME => $lastName,
            ServiceRequest::FIELD_PHONE => $phone,
            ServiceRequest::FIELD_DESCRIPTION => $description,
            ServiceRequest::FIELD_MEDIA_URL => $mediaUrl
        );

        try {
            return $this->postServiceRequest(
                ServiceRequest::fromArray($data, $this->serviceRequestRequiredFields),
                $format
            );
        } catch (Open311Exception $e) {
            throw $e;
        }
    }

    /**
     * Returns an array of valid service codes
     *
     * @return array
     */
    public function getValidServiceCodes()
    {
        $serviceList = $this->listServices();

        // Attempt to get service codes list from live data
        if (!is_null($serviceList) && is_array($serviceList)) {
            $validServiceCodes = array();

            foreach ($serviceList as $serviceDefinition) {

                if (isset($serviceDefinition[self::PARAM_SERVICE_CODE]) &&
                    !empty($serviceDefinition[self::PARAM_SERVICE_CODE])
                ){
                    $validServiceCodes[] = $serviceDefinition[self::PARAM_SERVICE_CODE];
                }

            }

            return $validServiceCodes;
        }

        // There was an error, return hard-coded entries
        return array(
            self::SERVICE_CODE_GRAFFITI_CITY_BRIDGE,
            self::SERVICE_CODE_GRAFFITI_CITY_LITTER_BIN,
            self::SERVICE_CODE_GRAFFITI_CITY_ROAD,
            self::SERVICE_CODE_GRAFFITI_CITY_SIDEWALK,
            self::SERVICE_CODE_POTHOLE
        );
    }
}
