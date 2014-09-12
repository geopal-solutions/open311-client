<?php

namespace GeoPal\Open311\Interfaces;

use GeoPal\Open311\ServiceRequest;

/**
 * Interface IClient
 *
 * @author Gabor.Zelei@geopal-solutions.com
 * @package GeoPal\Open311\Clients
 *
 * Interface definition for Open311 client libraries
 */
interface IOpen311Client
{
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
    );

    public function getAllServiceRequests(
        $serviceRequestId = null,
        $serviceCode = null,
        $startDate = null,
        $endDate = null,
        $status = null,
        $format = ''
    );

    public function getServiceDefinition($serviceCode, $format = '');
    public function getServiceRequest($serviceRequestId = null, $format = '');
    public function getServiceRequestIdFromToken($token = null, $format = '');
    public function getValidServiceCodes();
    public function listServices($format = '');
    public function postServiceRequest(ServiceRequest $serviceRequest, $format = '');
}
