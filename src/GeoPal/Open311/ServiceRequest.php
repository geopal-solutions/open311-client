<?php

namespace GeoPal\Open311;

use GeoPal\Open311\Exceptions\Open311Exception;

/**
 * Class ServiceRequest
 *
 * @author Gabor.Zelei@geopal-solutions.com
 * @package GeoPal\Open311
 *
 * Class to represent a service request entry in Open311
 */
class ServiceRequest
{
    const FIELD_ACCOUNT_ID = 'account_id';
    const FIELD_ADDRESS_ID = 'address_id';
    const FIELD_ADDRESS_STRING = 'address';
    const FIELD_AGENCY_RESPONSIBLE = 'agency_responsible';
    const FIELD_DESCRIPTION = 'description';
    const FIELD_DEVICE_ID = 'device_id';
    const FIELD_EMAIL = 'email';
    const FIELD_EXPECTED_DATETIME = 'expected_datetime';
    const FIELD_FIRST_NAME = 'first_name';
    const FIELD_LAST_NAME = 'last_name';
    const FIELD_LATITUDE = 'lat';
    const FIELD_LONGITUDE = 'long';
    const FIELD_MEDIA_URL = 'media_url';
    const FIELD_PHONE = 'phone';
    const FIELD_REQUESTED_DATETIME = 'requested_datetime';
    const FIELD_SERVICE_CODE = 'service_code';
    const FIELD_SERVICE_NAME = 'service_name';
    const FIELD_SERVICE_NOTICE = 'service_notice';
    const FIELD_SERVICE_REQUEST_ID = 'service_request_id';
    const FIELD_STATUS = 'status';
    const FIELD_STATUS_NOTES = 'status_notes';
    const FIELD_UPDATED_DATETIME = 'updated_datetime';
    const FIELD_ZIPCODE = 'zipcode';

    const ERROR_INVALID_INPUT_DATA = 'Invalid Input Data';
    const ERROR_REQUIRED_FIELD_MISSING = 'Required Field Missing: %s';

    /**
     * @var array
     */
    private $fields;

    /**
     * @var array
     */
    private $requiredFields = array(
        self::FIELD_SERVICE_CODE,
        self::FIELD_ADDRESS_STRING
    );

    /**
     * Constructor
     *
     * @param array $data
     * @param null $requiredFields
     * @throws Open311Exception
     */
    public function __construct($data = array(), $requiredFields = null)
    {
        $this->requiredFields = is_null($requiredFields) ? $this->requiredFields : $requiredFields;

        try {
            $this->setUpFromArray($data);
        } catch (Open311Exception $e) {
            throw $e;
        }
    }

    /**
     * Getter method for field values
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return (isset($this->fields[$key]) && !empty($this->fields[$key])) ? $this->fields[$key] : null;
    }

    /**
     * Static function to create a new instance from an array
     *
     * @param array $data
     * @param array $requiredFields
     * @throws Open311Exception
     * @return ServiceRequest
     */
    public static function fromArray($data, $requiredFields = null)
    {
        try {
            return new ServiceRequest($data, $requiredFields);
        } catch (Open311Exception $e) {
            throw $e;
        }
    }

    /**
     * Setter method for field values
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value = null)
    {
        if (is_string($key)) {
            $this->fields[$key] = $value;
        }
    }

    /**
     * Return stored values as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->fields;
    }

    /**
     * Configures values in the service request based on values in the input array
     *
     * @param array $data
     * @throws Open311Exception
     */
    private function setUpFromArray($data = array())
    {

        if (is_array($data) && !empty($data)) {

            // Check if all required fields are present and not empty
            foreach ($this->requiredFields as $requiredFieldName) {

                if (!isset($data[$requiredFieldName]) || empty($data[$requiredFieldName])) {
                    throw new Open311Exception(sprintf(self::ERROR_REQUIRED_FIELD_MISSING, $requiredFieldName));
                }

            }

            $this->fields = $data;
        } else {
            throw new Open311Exception(self::ERROR_INVALID_INPUT_DATA);
        }
    }
}
