<?php

namespace GeoPal\Tests\Open311\Clients;

use GeoPal\Open311\Clients\TorontoClient;
use GeoPal\Open311\Exceptions\Open311Exception;
use GeoPal\Open311\ServiceRequest;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;


/***
 *
 *
 *   NOTE: This test suite also contains test cases that are being run against
 *         the Toronto Open311 tests server, without emulating any calls.
 *
 */
class TorontoClientTest extends \PHPUnit_Framework_TestCase
{
    const TEST_SYSTEM_ENDPOINT_URL = 'https://secure.toronto.ca/open311test/ws';

    /**
     * @var \Guzzle\Http\Client
     */
    private $guzzleClient;

    /**
     * Set Up
     */
    public function setUp()
    {
        parent::setUp();

        // Guzzle client to be injected for live tests against the Toronto Open311 test instance
        $this->guzzleClient = new Client(self::TEST_SYSTEM_ENDPOINT_URL);
    }

    /**
     * @return array
     */
    public static function providerTestCreateServiceRequest()
    {
        $provider = array();

        // Case 1
        $provider[] = array(
            array(
                ServiceRequest::FIELD_SERVICE_CODE => TorontoClient::SERVICE_CODE_GRAFFITI_CITY_ROAD,
                ServiceRequest::FIELD_LATITUDE => 43.701553,
                ServiceRequest::FIELD_LONGITUDE => -79.520164,
                ServiceRequest::FIELD_ADDRESS_STRING => '1 KING ST, YORK, Central United Church',
                ServiceRequest::FIELD_ADDRESS_ID => 7074420,
                ServiceRequest::FIELD_EMAIL => 'test@geopaltest.com',
                ServiceRequest::FIELD_DEVICE_ID => '',
                ServiceRequest::FIELD_ACCOUNT_ID => '',
                ServiceRequest::FIELD_FIRST_NAME => 'John',
                ServiceRequest::FIELD_LAST_NAME => 'Doe',
                ServiceRequest::FIELD_PHONE => '',
                ServiceRequest::FIELD_DESCRIPTION => 'Description for Road - Graffiti Complaint"',
                ServiceRequest::FIELD_MEDIA_URL => ''
            ),
            true
        );

        // Case 2 - invalid address
        $provider[] = array(
            array(
                ServiceRequest::FIELD_SERVICE_CODE => TorontoClient::SERVICE_CODE_GRAFFITI_CITY_ROAD,
                ServiceRequest::FIELD_LATITUDE => 43.701553,
                ServiceRequest::FIELD_LONGITUDE => -79.520164,
                ServiceRequest::FIELD_ADDRESS_STRING => '',
                ServiceRequest::FIELD_EMAIL => 'test@geopaltest.com',
                ServiceRequest::FIELD_DEVICE_ID => '',
                ServiceRequest::FIELD_ACCOUNT_ID => '',
                ServiceRequest::FIELD_FIRST_NAME => 'John',
                ServiceRequest::FIELD_LAST_NAME => 'Doe',
                ServiceRequest::FIELD_PHONE => '',
                ServiceRequest::FIELD_DESCRIPTION => 'No description',
                ServiceRequest::FIELD_MEDIA_URL => ''
            ),
            false
        );

        // Case 3 - invalid lat lng
        $provider[] = array(
            array(
                ServiceRequest::FIELD_SERVICE_CODE => TorontoClient::SERVICE_CODE_GRAFFITI_CITY_ROAD,
                ServiceRequest::FIELD_LATITUDE => null,
                ServiceRequest::FIELD_LONGITUDE => null,
                ServiceRequest::FIELD_ADDRESS_STRING => '1 KING ST, YORK, Central United Church',
                ServiceRequest::FIELD_EMAIL => 'test@geopaltest.com',
                ServiceRequest::FIELD_DEVICE_ID => '',
                ServiceRequest::FIELD_ACCOUNT_ID => '',
                ServiceRequest::FIELD_FIRST_NAME => 'John',
                ServiceRequest::FIELD_LAST_NAME => 'Doe',
                ServiceRequest::FIELD_PHONE => '',
                ServiceRequest::FIELD_DESCRIPTION => 'No description',
                ServiceRequest::FIELD_MEDIA_URL => ''
            ),
            false
        );

        return $provider;
    }

    /**
     * @param array $input
     * @param bool $expectSuccess
     *
     * @covers GeoPal\Open311\Clients\TorontoClient::createServiceRequest
     * @dataProvider providerTestCreateServiceRequest
     */
    public function testCreateServiceRequest($input, $expectSuccess)
    {
        $stubResponse = array(
            array(ServiceRequest::FIELD_SERVICE_REQUEST_ID => 'stub_id')
        );

        /**
         * Create and set up fake guzzle client
         */
        $response = is_null($stubResponse)
            ? new Response(200)
            : new Response(200, null, json_encode($stubResponse));

        $mockPlugin = new MockPlugin();
        $mockPlugin->addResponse($response);

        $stubGuzzleClient = new Client();
        $stubGuzzleClient->addSubscriber($mockPlugin);

        /**
         * Create test client
         */
        $client = new TorontoClient('testing', $stubGuzzleClient);

        /**
         * Check call result
         */
        if ($expectSuccess) {
            $this->assertInstanceOf(
                '\GeoPal\Open311\ServiceRequestResponse',
                $client->createServiceRequest(
                    $input[ServiceRequest::FIELD_SERVICE_CODE],
                    $input[ServiceRequest::FIELD_LATITUDE],
                    $input[ServiceRequest::FIELD_LONGITUDE],
                    $input[ServiceRequest::FIELD_ADDRESS_STRING],
                    $input[ServiceRequest::FIELD_ADDRESS_ID],
                    $input[ServiceRequest::FIELD_EMAIL],
                    $input[ServiceRequest::FIELD_DEVICE_ID],
                    $input[ServiceRequest::FIELD_ACCOUNT_ID],
                    $input[ServiceRequest::FIELD_FIRST_NAME],
                    $input[ServiceRequest::FIELD_LAST_NAME],
                    $input[ServiceRequest::FIELD_PHONE],
                    $input[ServiceRequest::FIELD_DESCRIPTION],
                    $input[ServiceRequest::FIELD_MEDIA_URL]
                )
            );
        } else {
            $result = false;
            $exceptionThrown = false;

            try {
                // Throw an exception or change $result from false to null
                $result = $client->createServiceRequest(
                    $input[ServiceRequest::FIELD_SERVICE_CODE],
                    $input[ServiceRequest::FIELD_LATITUDE],
                    $input[ServiceRequest::FIELD_LONGITUDE],
                    $input[ServiceRequest::FIELD_ADDRESS_STRING],
                    $input[ServiceRequest::FIELD_EMAIL],
                    $input[ServiceRequest::FIELD_DEVICE_ID],
                    $input[ServiceRequest::FIELD_ACCOUNT_ID],
                    $input[ServiceRequest::FIELD_FIRST_NAME],
                    $input[ServiceRequest::FIELD_LAST_NAME],
                    $input[ServiceRequest::FIELD_PHONE],
                    $input[ServiceRequest::FIELD_DESCRIPTION],
                    $input[ServiceRequest::FIELD_MEDIA_URL]
                );
            } catch (Open311Exception $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue($exceptionThrown || is_null($result));
        }
    }


    ////////////////////////////////////
    //                                //
    //        LIVE TESTS BELOW        //
    //                                //
    ////////////////////////////////////


    /**
     * Tests retrieving a list of services from the
     * Toronto Open311 test system
     *
     * Note: This test makes a call against the
     * actual test system, no calls are emulated!
     */
//    public function testListServicesLive()
//    {
//        $client = new TorontoClient('testing', $this->guzzleClient);
//
//        // Do call and assert results
//        $result = $client->listServices();
//        $this->assertTrue(is_array($result));
//
//        foreach ($result as $serviceDescription) {
//            $this->assertArrayHasKey('description', $serviceDescription);
//            $this->assertArrayHasKey('group', $serviceDescription);
//            $this->assertArrayHasKey('keywords', $serviceDescription);
//            $this->assertArrayHasKey('metadata', $serviceDescription);
//            $this->assertArrayHasKey('service_code', $serviceDescription);
//            $this->assertArrayHasKey('service_name', $serviceDescription);
//            $this->assertArrayHasKey('type', $serviceDescription);
//        }
//    }

    /**
     * Tests retrieving a list of service requests
     * from the Toronto Open311 test system
     *
     * Note: This test makes a call against the
     * actual test system, no calls are emulated!
     */
//    public function testGetAllServiceRequestsLive()
//    {
//        $client = new TorontoClient('testing', $this->guzzleClient);
//
//        // Do call and assert results
//        $result = $client->getAllServiceRequests();
//        $this->assertTrue(is_array($result));
//
//        foreach ($result as $serviceRequest) {
//            $this->assertInstanceOf('\GeoPal\Open311\ServiceRequest', $serviceRequest);
//        }
//    }

    /**
     * Tests retrieving a randomly selected service
     * request from the Toronto Open311 test system
     *
     * Note: This test makes a call against the
     * actual test system, no calls are emulated!
     *
     * Currently, this test does not work, as the
     * test server seems to crash whenever this
     * data gets requested
     */
//    public function testGetServiceRequestLive()
//    {
//        $client = new TorontoClient('testing', $this->guzzleClient);
//
//        // Get a full list of service requests
//        $serviceRequests = $client->getAllServiceRequests();
//
//        /**
//         * Get the id of the last service request in the list
//         * @var ServiceRequest $selectedServiceRequest
//         */
//        $selectedServiceRequest = $serviceRequests[(count($serviceRequests) - 1)];
//        $serviceRequestId = $selectedServiceRequest->get(ServiceRequest::FIELD_SERVICE_REQUEST_ID);
//
//        $this->assertEquals($selectedServiceRequest, $client->getServiceRequest($serviceRequestId));
//    }

    /**
     * Tests creating a new service request in
     * the Toronto Open311 test system
     *
     * Note: This test makes a call against the
     * actual test system, no calls are emulated!
     *
     * @param array $input
     * @param bool $expectSuccess
     *
     * @dataProvider providerTestCreateServiceRequest
     */
//    public function testCreateServiceRequestLive($input, $expectSuccess)
//    {
//        $client = new TorontoClient('testing', $this->guzzleClient);
//
//        if ($expectSuccess) {
//            $response = $client->createServiceRequest(
//                $input[ServiceRequest::FIELD_SERVICE_CODE],
//                $input[ServiceRequest::FIELD_LATITUDE],
//                $input[ServiceRequest::FIELD_LONGITUDE],
//                $input[ServiceRequest::FIELD_ADDRESS_STRING],
//                $input[ServiceRequest::FIELD_EMAIL],
//                $input[ServiceRequest::FIELD_DEVICE_ID],
//                $input[ServiceRequest::FIELD_ACCOUNT_ID],
//                $input[ServiceRequest::FIELD_FIRST_NAME],
//                $input[ServiceRequest::FIELD_LAST_NAME],
//                $input[ServiceRequest::FIELD_PHONE],
//                $input[ServiceRequest::FIELD_DESCRIPTION],
//                $input[ServiceRequest::FIELD_MEDIA_URL]
//            );
//
//            $this->assertInstanceOf('\GeoPal\Open311\ServiceRequestResponse', $response);
//        } else {
//            $result = false;
//            $exceptionThrown = false;
//
//            try {
//                // Throw an exception or change $result from false to null
//                $result = $client->createServiceRequest(
//                    $input[ServiceRequest::FIELD_SERVICE_CODE],
//                    $input[ServiceRequest::FIELD_LATITUDE],
//                    $input[ServiceRequest::FIELD_LONGITUDE],
//                    $input[ServiceRequest::FIELD_ADDRESS_STRING],
//                    $input[ServiceRequest::FIELD_EMAIL],
//                    $input[ServiceRequest::FIELD_DEVICE_ID],
//                    $input[ServiceRequest::FIELD_ACCOUNT_ID],
//                    $input[ServiceRequest::FIELD_FIRST_NAME],
//                    $input[ServiceRequest::FIELD_LAST_NAME],
//                    $input[ServiceRequest::FIELD_PHONE],
//                    $input[ServiceRequest::FIELD_DESCRIPTION],
//                    $input[ServiceRequest::FIELD_MEDIA_URL]
//                );
//            } catch (Open311Exception $e) {
//                $exceptionThrown = true;
//            }
//
//            $this->assertTrue($exceptionThrown || is_null($result));
//        }
//    }
}
