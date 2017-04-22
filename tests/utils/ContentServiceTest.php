<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ContentServiceTest extends TestCase
{
    private $dir = 'tests-data/public';

    public function testGetAll()
    {
        $service = new ContentService($this->dir);
        $response = $service->getAll('calendar/index.json');


        $this->assertEquals($response->getCode(), 200);

        $this->assertTrue(
          strstr($response->getResult(), '"id":"1"') != ''
        );


        $this->assertTrue(
          strstr($response->getResult(), '"id":"2"') != ''
        );


    }

    public function testGetItemFromList()
    {
        $service = new ContentService($this->dir);
        $response = $service->get('calendar/index.json', 'id', '1');

        $this->assertEquals($response->getCode(), 200);

        $this->assertJsonStringEqualsJsonString(
        json_encode(json_decode('{"id":"1","date":"201509","activity":"activitya","title":"some seminar of activity A","organization":"Some org","description":"some infos","url":"","location":"","startdate":"","enddate":"","updated":"","updatedby":""}')),
        $response->getResult()
      );
    }

    public function testPost()
    {
        $recordStr = '{"id":"10","date":"201509","activity":"activitya","title":"some seminar of activity A","organization":"Some org","description":"some infos","url":"","location":"","startdate":"","enddate":"","updated":"","updatedby":""}';
        $service = new ContentService($this->dir);
        $response = $service->post('calendar', 'id', $recordStr);

        $file = $this->dir . '/calendar/10.json';

        $this->assertEquals($response->getCode(), 200);


        $this->assertJsonStringEqualsJsonFile(
            $file , $recordStr
        );
    }

    public function testPublish()
    {

        $service = new ContentService($this->dir);
        $response = $service->publish('calendar', 'id', '10');
        echo $response->getMessage();
        echo $response->getResult();
        $this->assertEquals($response->getCode(), 200);



    }


}
