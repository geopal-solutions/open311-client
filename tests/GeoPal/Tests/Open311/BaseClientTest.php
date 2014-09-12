<?php

namespace GeoPal\Tests\Open311;

use GeoPal\Open311\Exceptions\Open311Exception;
use GeoPal\Open311\BaseClient;
use GeoPal\Open311\ServiceRequest;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;

/**
 * Class BaseClientTest
 *
 * @author Gabor.Zelei@geopal-solutions.com
 * @package tests\Open311
 */
class BaseClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \GeoPal\Open311\BaseClient::__construct
     */
    public function testConstructor()
    {
        $stubClient = new StubClient();

        $this->assertEquals('toronto.ca', $this->getReflectionPropertyValue($stubClient, 'jurisdictionId'));
        $this->assertEquals(null, $this->getReflectionPropertyValue($stubClient, 'apiKey'));
        $this->assertEquals(null, $this->getReflectionPropertyValue($stubClient, 'serviceRequestRequiredFields'));
        $this->assertEquals(
            'https://secure.toronto.ca/open311test/ws',
            $this->getReflectionPropertyValue($stubClient, 'endPoint')
        );

        $stubClient->setApiKey('testApiKey');
        $this->assertEquals('testApiKey', $this->getReflectionPropertyValue($stubClient, 'apiKey'));
    }

    /**
     * @covers \GeoPal\Open311\BaseClient::getPostParamsArray
     */
    public function testGetPostParamsArray()
    {
        $stubClient = new StubClient();
        $stubClient->setApiKey('testApiKey');

        $standardParams = $this->getReflectionMethodResult($stubClient, 'getPostParamsArray');

        $this->assertContains('toronto.ca', $standardParams);
        $this->assertContains('testApiKey', $standardParams);
        $this->assertContains('jurisdiction_id', array_keys($standardParams));
        $this->assertContains('api_key', array_keys($standardParams));
    }

    /**
     * @covers \GeoPal\Open311\BaseClient::getAllServiceRequests
     */
    public function testGetAllServiceRequests()
    {
        /**
         * Fake values returned from server
         */
        $stubReturnValue = array(
            'service_requests' => array(
                array(
                    'service_code' => 'stub_service_code_1',
                    'address' => 'stub_address_string_1'
                ),
                array(
                    'service_code' => 'stub_service_code_2',
                    'address' => 'stub_address_string_2'
                )
            )
        );

        /**
         * Create test client and do call
         */
        $stubClient = new StubClient($this->getStubGuzzleClient($stubReturnValue));
        $result = $stubClient->getAllServiceRequests();

        /**
         * Verify results
         */
        $this->assertTrue(is_array($result));

        foreach ($result as $entry) {
            $this->assertInstanceOf('\GeoPal\Open311\ServiceRequest', $entry);
        }
    }

    /**
     * @return array
     */
    public function providerTestGetServiceRequestIdFromToken()
    {
        $params = array();

        // Case 1
        $params[] = array(
            'testToken',
            'testRequestId'
        );

        // Case 2
        $params[] = array(
            null,
            null
        );

        return $params;
    }

    /**
     * @param array $token
     * @param mixed $expectedResult
     *
     * @covers \GeoPal\Open311\BaseClient::getServiceRequestIdFromToken
     * @dataProvider providerTestGetServiceRequestIdFromToken
     */
    public function testGetServiceRequestIdFromToken($token, $expectedResult)
    {
        if (!is_null($token)) {
            $stubReturnValue = array(array(BaseClient::PARAM_SERVICE_REQUEST_ID => 'testRequestId'));
        } else {
            $stubReturnValue = array(array(BaseClient::PARAM_SERVICE_REQUEST_ID => null));
        }

        /**
         * Create test client and do call
         */
        $stubClient = new StubClient($this->getStubGuzzleClient($stubReturnValue));
        $result = $stubClient->getServiceRequestIdFromToken($token);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function providerTestGetServiceRequest()
    {
        $params = array();

        // Case 1
        $params[] = array(
            'testServiceRequestId',
            true
        );

        // Case 2
        $params[] = array(
            null,
            false
        );

        return $params;
    }

    /**
     * @param array $serviceRequestId
     * @param mixed $expectSuccess
     *
     * @covers \GeoPal\Open311\BaseClient::getServiceRequest
     * @dataProvider providerTestGetServiceRequest
     */
    public function testGetServiceRequest($serviceRequestId, $expectSuccess)
    {
        if ($expectSuccess) {
            $stubReturnValue = array(
                'service_requests' => array(
                    array(
                        'service_code' => 'stub_service_code_1',
                        'address' => 'stub_address_string_1'
                    )
                )
            );
        } else {
            $stubReturnValue = array(array());
        }

        /**
         * Create test client and do call
         */
        $stubClient = new StubClient($this->getStubGuzzleClient($stubReturnValue));
        $result = $stubClient->getServiceRequest($serviceRequestId);

        if ($expectSuccess) {
            $this->assertNotNull($result);
            $this->assertInstanceOf('\GeoPal\Open311\ServiceRequest', $result);
        } else {
            $this->assertNull($result);
        }
    }

    /**
     * @covers \GeoPal\Open311\BaseClient::listServices
     */
    public function testListServices()
    {
        $stubServiceList = array(
            array(
                "service_code" => 001,
                "service_name" => "Test service 1",
                "description" => "Test service for listing services 1",
                "metadata" => true,
                "type" => "realtime",
                "keywords" => "lorem, ipsum, dolor",
                "group" => "test group"
            ),
            array(
                "service_code" => 002,
                "service_name" => "Test service 2",
                "description" => "Test service for listing services 2",
                "metadata" => true,
                "type" => "realtime",
                "keywords" => "lorem, ipsum, dolor",
                "group" => "test group"
            )
        );

        /**
         * Create test client and do call
         */
        $stubClient = new StubClient($this->getStubGuzzleClient($stubServiceList));
        $this->assertEquals($stubServiceList, $stubClient->listServices());
    }

    /**
     * @return array
     */
    public function providerTestPostServiceRequest()
    {
        $provider = array();

        // Case 1
        $provider[] = array(
            ServiceRequest::fromArray(array('service_code' => 'test code', 'address' => 'test address')),
            true
        );

        // Case 2
        $provider[] = array(
            array('invalid' => 'data'),
            false
        );

        return $provider;
    }

    /**
     * @param mixed $serviceRequest
     * @param bool $expectSuccess
     *
     * @covers \GeoPal\Open311\BaseClient::postServiceRequest
     * @dataProvider providerTestPostServiceRequest
     */
    public function testPostServiceRequest($serviceRequest, $expectSuccess)
    {
        $stubResponse = array(array(
            array(ServiceRequest::FIELD_SERVICE_REQUEST_ID => 'stub_id')
        ));

        /**
         * Create test client and do call
         */
        $stubClient = new StubClient($this->getStubGuzzleClient($stubResponse));

        if ($expectSuccess) {
            $response = $stubClient->postServiceRequest($serviceRequest);
            $this->assertInstanceOf('\GeoPal\Open311\ServiceRequestResponse', $response);
        } else {
            $result = false;
            $exceptionThrown = false;

            try {
                // Throw an exception or change $result from false to null
                $result = $stubClient->postServiceRequest(ServiceRequest::fromArray($serviceRequest));
            } catch (Open311Exception $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue($exceptionThrown || is_null($result));
        }
    }


    ///// Helper functions /////


    /**
     * Gets the value of a private or protected property of an object
     *
     * @param mixed $object
     * @param string $propertyName
     * @return mixed
     */
    private function getReflectionPropertyValue($object, $propertyName)
    {
        $reflectionProperty = new \ReflectionProperty(get_class($object), $propertyName);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }

    /**
     * Gets the returned result of a private method
     *
     * @param mixed $object
     * @param string $methodName
     * @param array $params
     * @return mixed
     */
    private function getReflectionMethodResult($object, $methodName, $params = array())
    {
        $reflectionMethod = new \ReflectionMethod(get_class($object), $methodName);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $params);
    }

    /**
     * Creates and sets up fake guzzle client with one fake response
     *
     * @param array $stubResponse
     * @return Client
     */
    private function getStubGuzzleClient(array $stubResponse = null)
    {
        $response = is_null($stubResponse)
            ? new Response(200)
            : new Response(200, null, json_encode($stubResponse));

        $mockPlugin = new MockPlugin();
        $mockPlugin->addResponse($response);

        $stubClient = new Client();
        $stubClient->addSubscriber($mockPlugin);

        return $stubClient;
    }
}
