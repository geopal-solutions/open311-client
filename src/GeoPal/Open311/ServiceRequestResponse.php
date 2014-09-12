<?php

namespace GeoPal\Open311;

/**
 * Class ServiceRequestResponse
 *
 * @author Gabor.Zelei@geopal-solutions.com
 * @package GeoPal\Open311
 *
 * Class to represent server response when attempting
 * to create a new service request
 */
class ServiceRequestResponse
{
    const FIELD_ACCOUNT_ID = 'account_id';
    const FIELD_SERVICE_NOTICE = 'service_notice';
    const FIELD_SERVICE_REQUEST_ID = 'service_request_id';
    const FIELD_TOKEN = 'token';

    /**
     * @var array
     */
    private $params;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->params = is_array($data) ? $data : array();
    }

    /**
     * Getter method for stored values
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return (isset($this->params[$key]) && !empty($this->params[$key])) ? $this->params[$key] : null;
    }

    /**
     * Static function to create a new instance from an array
     *
     * @param array $data
     * @return ServiceRequestResponse
     */
    public static function fromArray($data)
    {
        return new ServiceRequestResponse($data);
    }

    /**
     * Return stored values as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->params;
    }
}
