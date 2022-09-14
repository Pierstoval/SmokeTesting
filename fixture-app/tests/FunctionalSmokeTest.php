<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalSmokeTest extends WebTestCase
{
    use FunctionalSmokeTester;

    public function testGet200(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/200')
                ->expectRouteName('get_200')
                ->expectStatusCode(200)
                ->expectTextToBePresent('200')
        );
    }

    public function testGetWithEmptyPayload(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/payload')
                ->withPayload('')
                ->expectRouteName('get_with_payload')
                ->expectStatusCode(400)
                ->expectTextToBePresent('')
        );
    }

    public function testGetWithPayload(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/payload')
                ->withPayload($payload = 'My payload')
                ->expectRouteName('get_with_payload')
                ->expectStatusCode(200)
                ->expectTextToBePresent($payload)
        );
    }

    public function testGetWithValidJson(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/valid')
                ->expectRouteName('json_valid')
                ->expectStatusCode(200)
                ->expectJsonParts([
                    'message' => 'Ok!',
                    'code' => 200,
                ])
        );
    }

    public function testGetWithMissingJsonResponseHeader(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/missing_header')
                ->expectRouteName('json_missing_header')
                ->expectStatusCode(200)
                ->expectTextToBePresent('{"message":"I miss the JSON response header!","code":200}')
                ->expectIsJsonResponse()
        );
    }

    public function testGetWithInvalidJson(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/invalid')
                ->expectRouteName('json_invalid')
                ->expectStatusCode(200)
                ->expectTextToBePresent('{"message":')
                ->expectIsJsonResponse()
        );
    }
}
