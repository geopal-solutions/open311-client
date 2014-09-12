<?php

namespace GeoPal\Open311;

use GeoPal\Open311\Exceptions\Open311Exception;
use Guzzle\Common\Exception\GuzzleException;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

/**
 * Class BaseClient
 *
 * @author Gabor.Zelei@geopal-solutions.com
 * @package Repositories\WebServices\Open311
 *
 * Open311 Base Client Class
 */
abstract class BaseClient
{
    const COMMAND_SERVICE_REQUESTS = 'requests';
    const COMMAND_SERVICES = 'services';
    const COMMAND_TOKENS = 'tokens';

    const ERROR_INVALID_COMMAND = 'Invalid Command';
    const ERROR_INVALID_CONFIGURATION_VALUE = 'Invalid Configuration Value: %s';
    const ERROR_INVALID_SERVICE_REQUEST = 'Invalid Service Request.';

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';

    const LABEL_CLOSED = 'closed';
    const LABEL_OPEN = 'open';
    const LABEL_SERVICE_REQUESTS = 'service_requests';

    const PARAM_API_KEY = 'api_key';
    const PARAM_END_DATE = 'end_date';
    const PARAM_JURISDICTION_ID = 'jurisdiction_id';
    const PARAM_SERVICE_CODE = 'service_code';
    const PARAM_SERVICE_REQUEST_ID = 'service_request_id';
    const PARAM_START_DATE = 'start_date';
    const PARAM_STATUS = 'status';

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    protected $endPoint;

    /**
     * @var string
     */
    protected $jurisdictionId;

    /**
     * A list of required fields for service requests
     * Leave as null for defaults
     *
     * @var array
     */
    protected $serviceRequestRequiredFields = null;

    /**
     * @param Client|null $guzzleClient
     * @throws Open311Exception
     */
    public function __construct(Client $guzzleClient = null)
    {
        try {
            $this->checkConfiguration();
            $this->client = is_null($guzzleClient) ? new Client($this->endPoint) : $guzzleClient;
        } catch (Open311Exception $e) {
            throw $e;
        } catch (GuzzleException $e) {
            throw new Open311Exception($e->getMessage());
        }
    }

    /**
     * Returns an array of ServiceRequest objects filtered
     * based on passed criteria
     *
     * @param string|null $serviceRequestId
     * @param string|null $serviceCode
     * @param \DateTime|string|null $startDate
     * @param \DateTime|string|null $endDate
     * @param string|null $status
     * @param string $format
     * @return array|null
     */
    public function getAllServiceRequests(
        $serviceRequestId = null,
        $serviceCode = null,
        $startDate = null,
        $endDate = null,
        $status = null,
        $format = self::FORMAT_JSON
    ){
        // Create a list of params to pass to the call.
        $filters = array(
            self::PARAM_SERVICE_REQUEST_ID => $serviceRequestId,
            self::PARAM_SERVICE_CODE => $serviceCode,
            self::PARAM_START_DATE => $startDate instanceof \DateTime ? $startDate->format(\DateTime::W3C) : $startDate,
            self::PARAM_END_DATE => $endDate instanceof \DateTime ? $endDate->format(\DateTime::W3C) : $endDate,
            self::PARAM_STATUS => in_array($status, array(self::LABEL_CLOSED, self::LABEL_OPEN)) ? $status : null
        );

        // Only pass valid values, no nulls or empty strings
        $params = array();

        foreach ($filters as $paramName => $paramValue) {
            if (!empty($paramValue)) {
                $params[$paramName] = $paramValue;
            }
        }

        try {
            $returnBuffer = array();
            $response = $this->get(self::COMMAND_SERVICE_REQUESTS, $format, $params);

            if (!is_null($response)) {
                $responseData = $this->responseToArray($response);

                if ($this->validResponseArray($responseData)) {

                    foreach ($responseData as $serviceRequestData) {
                        $returnBuffer[] = ServiceRequest::fromArray(
                            $serviceRequestData,
                            $this->serviceRequestRequiredFields
                        );
                    }

                    return $returnBuffer;
                }

            }

        } catch (GuzzleException $e) {
            return null;
        } catch (Open311Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Returns data on a service definition
     *
     * @param string $serviceCode
     * @param string $format
     * @return array|null
     */
    public function getServiceDefinition($serviceCode, $format = self::FORMAT_JSON)
    {
        try {
            $response = $this->get(implode('/', array(self::COMMAND_SERVICES, $serviceCode)), $format);

            if (!is_null($response)) {
                return $response->json();
            } else {
                return null;
            }

        } catch (GuzzleException $e) {
            return null;
        }
    }

    /**
     * Queries a service request id based on a token returned at service request creation
     *
     * @param string|null $token
     * @param string $format
     * @return string|integer|null
     */
    public function getServiceRequestIdFromToken($token = null, $format = self::FORMAT_JSON)
    {
        if (!empty($token)) {
            try {
                $response = $this->get(implode('/', array(self::COMMAND_TOKENS, $token)), $format);

                if (!is_null($response)) {
                    $responseArray = $response->json();

                    if ($this->validResponseArray($responseArray)) {
                        return $responseArray[0][self::PARAM_SERVICE_REQUEST_ID];
                    }
                }

            } catch (GuzzleException $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Returns a service request by id
     *
     * @param string|integer|null $serviceRequestId
     * @param string $format
     * @return ServiceRequest|null
     */
    public function getServiceRequest($serviceRequestId = null, $format = self::FORMAT_JSON)
    {
        if (!empty($serviceRequestId)) {
            try {
                $uri = implode('/', array(self::COMMAND_SERVICE_REQUESTS, $serviceRequestId));
                $response = $this->get($uri, $format);

                if (!is_null($response)) {
                    $responseData = $this->responseToArray($response);

                    if ($this->validResponseArray($responseData)) {
                        return ServiceRequest::fromArray($responseData[0], $this->serviceRequestRequiredFields);
                    }

                }

            } catch (GuzzleException $e) {
                return null;
            } catch (Open311Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Returns a list of supported services as an array
     *
     * @param string $format
     * @return array|null
     */
    public function listServices($format = self::FORMAT_JSON)
    {
        try {
            $response = $this->get(self::COMMAND_SERVICES, $format);

            if (!is_null($response)) {
                return $response->json();
            } else {
                return null;
            }

        } catch (GuzzleException $e) {
            return null;
        }
    }

    /**
     * Posts a new service request to Open311
     *
     * @param ServiceRequest $serviceRequest
     * @param string $format
     * @return ServiceRequestResponse|null
     * @throws Open311Exception
     */
    public function postServiceRequest(ServiceRequest $serviceRequest, $format = self::FORMAT_JSON)
    {
        if ($serviceRequest instanceof ServiceRequest) {
            $response = $this->post(self::COMMAND_SERVICE_REQUESTS, $format, $serviceRequest->toArray());

            if (!is_null($response)) {
                $responseData = $response->json();

                if (is_array($responseData) && isset($responseData[0])) {
                    return ServiceRequestResponse::fromArray($responseData[0]);
                }
            }

        } else {
            throw new Open311Exception(self::ERROR_INVALID_SERVICE_REQUEST);
        }

        return null;
    }

    /**
     * Loops through required configuration properties for the client and
     * throws an exception if any of them are missing or invalid
     *
     * @throws Open311Exception
     */
    private function checkConfiguration()
    {
        $requiredParams = array('endPoint', 'jurisdictionId');

        foreach ($requiredParams as $requiredParam) {
            if (!isset($this->{$requiredParam}) || empty($this->{$requiredParam})) {
                throw new Open311Exception(sprintf(self::ERROR_INVALID_CONFIGURATION_VALUE, $requiredParam));
            }
        }
    }

    /**
     * Makes a HTML GET call through Guzzle and returns the
     * resulting Response object
     *
     * @param string $command
     * @param string $format
     * @param array $params
     * @return Response|null
     * @throws Open311Exception
     */
    private function get($command, $format, $params = array())
    {
        if (empty($command) || empty($format)) {
            throw new Open311Exception(self::ERROR_INVALID_COMMAND);
        }

        $uri = implode(
            '?',
            array(
                implode('.', array($command, $format)),
                http_build_query(array('jurisdiction_id' => $this->jurisdictionId) + $params)
            )
        );

        $request = $this->client->get($uri);
        $request->send();
        return $request->getResponse();
    }

    /**
     * Creates and returns an array with standard parameters that should be
     * submitted with all post calls
     *
     * @return array
     */
    private function getPostParamsArray()
    {
        $standardPostParams = array(self::PARAM_JURISDICTION_ID => $this->jurisdictionId);

        if (!empty($this->apiKey)) {
            $standardPostParams[self::PARAM_API_KEY] = $this->apiKey;
        }

        return $standardPostParams;
    }

    /**
     * Makes a HTML POST call through Guzzle and returns the
     * resulting Response object
     *
     * @param string $command
     * @param string $format
     * @param array $params
     * @return Response|null
     * @throws Open311Exception
     */
    private function post($command, $format, $params)
    {
        if (empty($command) || empty($format)) {
            throw new Open311Exception(self::ERROR_INVALID_COMMAND);
        }

        $params = array_merge($this->getPostParamsArray() + $params);
        $uri = implode('.', array($command, $format));

        $request = $this->client->post($uri, null, $params);
        $request->send();

        return $request->getResponse();
    }

    /**
     * Gets service request data as an array
     * from a response object
     *
     * @param Response|null $response
     * @return array|null
     */
    private function responseToArray(Response $response = null)
    {
        if (!is_null($response) && ($response instanceof Response)) {
            $responseArray = $response->json();
            return isset($responseArray[self::LABEL_SERVICE_REQUESTS])
                ? $responseArray[self::LABEL_SERVICE_REQUESTS]
                : null;
        }

        return null;
    }

    /**
     * Verifies if a responseArray is of the correct structure
     *
     * @param array $responseArray
     * @return bool
     */
    private function validResponseArray($responseArray)
    {
        return (is_array($responseArray) && is_array($responseArray[0]));
    }
}
