<?php

use Loris\Discovery;
use Loris\Utility;

use Loris\Resource\GeneratedResource;

class GeneratedResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Perform model registration for Discovery
     */
    protected function setUp()
    {
        Discovery::register(
            GeneratedResource::URI, '\\Loris\\Resource\\GeneratedResource'
        );

        // References used by the generated resource that don't really exist
        $fakepaths = array(
            '/array-collection/{id}',
            '/array-collection/{idLeft}/{idRight}',
            '/array-resource/{idLeft}/{idRight}',
            '/object-collection/{id}',
            '/object-collection/{idLeft}/{idRight}',
            '/object-resource/{idLeft}/{idRight}',
            '/object-resource/{id}',
            '/array-resource/{id}',
            '/collection/{id}',
            '/collection/{idLeft}/{idRight}',
            '/resource/{idLeft}/and/{idRight}',
            '/resource/{id}',
            '/resource'
        );

        foreach ($fakepaths as $i => $fakepath) {
            // TODO: Eventually point these to ExternalResource/RemoteResource 
            Discovery::register($fakepath, '\\Loris\\Resource\\GeneratedResource');
        }
    }

    /**
     * Create a new instance of a GeneratedResource resource.
     *
     * @return GeneratedResource
     */
    public function testCreate()
    {
        $generatedResource = new GeneratedResource(array('idLeft'=>'12345', 'idRight'=>'10'));

        // Test metadata
        $this->assertEquals(array('idLeft'=>'12345', 'idRight'=>'10'), $generatedResource->ids());
        $this->assertSame('/test/12345/foo/10', $generatedResource->meta->uri);

        return $generatedResource;
    }

    /**
     * Query for a specific GeneratedResource resource.
     *
     * @param GeneratedResource $generatedResource
     *
     * @depends testCreate
     *
     * @return GeneratedResource
     */
    public function testQuery(GeneratedResource $generatedResource)
    {
        GeneratedResource::query(array($generatedResource));

        // Test metadata
        $this->assertEquals(array('idLeft'=>'12345', 'idRight'=>'10'), $generatedResource->ids());
        $this->assertSame('/test/12345/foo/10', $generatedResource->meta->uri);

        // Test simple attributes
        $this->assertSame('Hello World', $generatedResource->stringProp);
        $this->assertSame(14, $generatedResource->numberProp);
        $this->assertSame(false, $generatedResource->boolProp);
        $this->assertInstanceOf('\\DateTime', $generatedResource->dateProp);
        $this->assertSame('08-14-1989', $generatedResource->dateProp->format('m-d-Y'));

        // Test simple arrays
        $this->assertEquals(2, count($generatedResource->arrayOfStringProp));
        $this->assertSame(array("foo", "bar"), $generatedResource->arrayOfStringProp);

        $this->assertEquals(5, count($generatedResource->arrayOfNumberProp));
        $this->assertSame(array(1,2,3,4,5), $generatedResource->arrayOfNumberProp);

        $this->assertEquals(2, count($generatedResource->arrayOfDateProp));
        $this->assertInstanceOf('\\DateTime', $generatedResource->arrayOfDateProp[0]);
        $this->assertInstanceOf('\\DateTime', $generatedResource->arrayOfDateProp[1]);

        // Test array of resources
        $this->assertEquals(2, count($generatedResource->arrayOfResourceProp));
        $this->assertInstanceOf(
            '\\Loris\\Resource\\Base\\Meta', 
            $generatedResource->arrayOfResourceProp[0]
        );
        $this->assertInstanceOf(
            '\\Loris\\Resource\\Base\\Meta', 
            $generatedResource->arrayOfResourceProp[1]
        );

        $this->assertEquals(array('id' => 'AR-12345-1'), $generatedResource->arrayOfResourceProp[0]->ids());
        $this->assertEquals(array('id' => 'AR-12345-2'), $generatedResource->arrayOfResourceProp[1]->ids());

        $this->assertEquals(1, count($generatedResource->arrayOfCollectionProp));
        $this->assertInstanceOf(
            '\\Loris\\Resource\\Base\\MetaCollection', 
            $generatedResource->arrayOfCollectionProp[0]
        );
        $this->assertEquals(array('id' => 'AC-12345'), $generatedResource->arrayOfCollectionProp[0]->ids());

        // Test object
        $this->assertInstanceOf('\\stdClass', $generatedResource->objectProp);
        $this->assertSame(true, $generatedResource->objectProp->opBoolProp);
        
        $this->assertInstanceOf('\\DateTime', $generatedResource->objectProp->opDateProp);
        $this->assertSame('06-01-2007', $generatedResource->objectProp->opDateProp->format('m-d-Y'));

        $this->assertSame(12, $generatedResource->objectProp->opNumberProp);
        $this->assertSame('User is OP', $generatedResource->objectProp->opStringProp);
        
        $this->assertInstanceOf(
            '\\Loris\\Resource\\Base\\MetaCollection', 
            $generatedResource->objectProp->opCollectionProp
        );
        $this->assertEquals(array('id' => 'OPC-12345'), $generatedResource->objectProp->opCollectionProp->ids());

        $this->assertInstanceOf(
            '\\Loris\\Resource\\Base\\Meta', 
            $generatedResource->objectProp->opResourceProp
        );
        $this->assertEquals(array('id' => 'OPR-12345'), $generatedResource->objectProp->opResourceProp->ids());

        // Test array of objects

        $this->assertEquals(1, count($generatedResource->arrayOfObjectProp));
        $this->assertInstanceOf('\\stdClass', $generatedResource->arrayOfObjectProp[0]);
        $this->assertSame(true, $generatedResource->arrayOfObjectProp[0]->aopBoolProp);
        $this->assertSame(55, $generatedResource->arrayOfObjectProp[0]->aopNumberProp);
        $this->assertSame('A string', $generatedResource->arrayOfObjectProp[0]->aopStringProp);

        $this->assertInstanceOf('\\DateTime', $generatedResource->arrayOfObjectProp[0]->aopDateProp);
        $this->assertSame('12-31-2015', $generatedResource->arrayOfObjectProp[0]->aopDateProp->format('m-d-Y'));

        $this->assertInstanceOf(
            '\\Loris\\Resource\\Base\\Meta', 
            $generatedResource->arrayOfObjectProp[0]->aopResourceProp
        );
        $this->assertEquals(array('id' => 'AOPR-12345'), $generatedResource->arrayOfObjectProp[0]->aopResourceProp->ids());

        $this->assertInstanceOf(
            '\\Loris\\Resource\\Base\\MetaCollection', 
            $generatedResource->arrayOfObjectProp[0]->aopCollectionProp
        );
        $this->assertEquals(array('id' => 'AOPC-12345'), $generatedResource->arrayOfObjectProp[0]->aopCollectionProp->ids());

        return $generatedResource;
    }

    /**
     * Test if the generated JSON matches expectations.
     * We use crc32() to speed/clean things up, rather than a straight string compare.
     * 
     * @depends testQuery
     */
    public function testSerialize(GeneratedResource $generatedResource)
    {
        $serialized = $generatedResource->serialize();

        $json = json_encode($serialized);
        $f = fopen('generatedResource.json', 'w');
        fwrite($f, $json);
        fclose($f);
        $this->assertEquals(3135752513, crc32($json));
    }

    /**
     * 
     */
    public function testExpandResource()
    {
        $generatedResource = new GeneratedResource(array('idLeft'=>'12345', 'idRight'=>'10'));

        $generatedResource->expand(array(
            'compositeResourceProp' => true
        ));

        GeneratedResource::query(array($generatedResource));

        $serialized = $generatedResource->serialize();

        // TODO: Better assertions. I just used a visual test against the
        // generated JSON, but we need to automate this better. 
        $json = json_encode($serialized);
        $f = fopen('generatedResource-compositeResourceProp.json', 'w');
        fwrite($f, $json);
        fclose($f);

        // Test metadata
        $this->assertEquals(
            array('idLeft' => 'CR-LEFT', 'idRight' => 'CR-RIGHT'),
            $generatedResource->compositeResourceProp->ids()
        );

        $this->assertEquals(
            '/test/CR-LEFT/foo/CR-RIGHT',
            $generatedResource->compositeResourceProp->meta->uri
        );

        // Pick a random property to test (should exist since we expanded)
        $this->assertEquals(
            'Hello World', 
            $generatedResource->compositeResourceProp->stringProp
        );

        // Test serialization results
        $this->assertEquals(2563886957, crc32($json));
    }

    public function testDeepExpandResource()
    {
        $generatedResource = new GeneratedResource(array('idLeft'=>'12345', 'idRight'=>'10'));

        $generatedResource->expand(array(
            'compositeResourceProp' => array(
                'compositeResourceProp' => true
            )
        ));

        GeneratedResource::query(array($generatedResource));

        $serialized = $generatedResource->serialize();

        // TODO: Better assertions. I just used a visual test against the 
        // generated JSON, but we need to automate this better. 
        $json = json_encode($serialized);
        $f = fopen('generatedResource-compositeResourceProp-compositeResourceProp.json', 'w');
        fwrite($f, $json);
        fclose($f);

        // Test metadata
        $this->assertEquals(
            array('idLeft' => 'CR-LEFT', 'idRight' => 'CR-RIGHT'),
            $generatedResource->compositeResourceProp->ids()
        );

        $this->assertEquals(
            '/test/CR-LEFT/foo/CR-RIGHT',
            $generatedResource->compositeResourceProp->meta->uri
        );

        // Pick a random property to test (should exist since we expanded)
        $this->assertEquals(
            'Hello World', 
            $generatedResource->compositeResourceProp->stringProp
        );

        // Test serialization results
        $this->assertEquals(3953406959, crc32($json));
    }

    public function testExpandResourceArray()
    {
        $generatedResource = new GeneratedResource(array('idLeft'=>'12345', 'idRight'=>'10'));

        $generatedResource->expand(array(
            'arrayOfCompositeResourceProp' => true
        ));

        GeneratedResource::query(array($generatedResource));

        $serialized = $generatedResource->serialize();

        // TODO: Better assertions. I just used a visual test against the 
        // generated JSON, but we need to automate this better. 
        $json = json_encode($serialized);
        $f = fopen('generatedResource-arrayOfCompositeResourceProp.json', 'w');
        fwrite($f, $json);
        fclose($f);

        $this->assertEquals(1086169428, crc32($json));
    }

    public function testDeepExpandResourceArray()
    {
        $generatedResource = new GeneratedResource(array('idLeft'=>'12345', 'idRight'=>'10'));

        $generatedResource->expand(array(
            'arrayOfCompositeResourceProp' => array(
                'arrayOfCompositeResourceProp' => true
            )
        ));

        GeneratedResource::query(array($generatedResource));

        $serialized = $generatedResource->serialize();

        // TODO: Better assertions. I just used a visual test against the 
        // generated JSON, but we need to automate this better. 
        $json = json_encode($serialized);
        $f = fopen('generatedResource-arrayOfCompositeResourceProp-arrayOfCompositeResourceProp.json', 'w');
        fwrite($f, $json);
        fclose($f);

        $this->assertEquals(2287707026, crc32($json));
    }
}
