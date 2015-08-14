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
            '/array-resource/{id}',
            '/object-collection/{id}',
            '/object-resource/{id}',
            '/collection/{id}',
            '/resource/{id}'
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
        $generatedResource = new GeneratedResource('12345');

        // Test metadata
        $this->assertEquals('12345', $generatedResource->id());
        $this->assertEquals('/test/12345', $generatedResource->meta->uri);

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
        $this->assertEquals('12345', $generatedResource->id());
        $this->assertEquals('/test/12345', $generatedResource->meta->uri);

        // Test simple attributes
        $this->assertEquals('Hello World', $generatedResource->stringProp);
        $this->assertEquals(14, $generatedResource->numberProp);
        $this->assertEquals(false, $generatedResource->boolProp);
        $this->assertInstanceOf('\DateTime', $generatedResource->dateProp);
        $this->assertEquals('08-14-1989', $generatedResource->dateProp->format('m-d-Y'));

        // Test simple arrays
        $this->assertEquals(2, count($generatedResource->arrayOfStringProp));
        $this->assertSame(array("foo", "bar"), $generatedResource->arrayOfStringProp);

        $this->assertEquals(5, count($generatedResource->arrayOfNumberProp));
        $this->assertSame(array(1,2,3,4,5), $generatedResource->arrayOfNumberProp);

        $this->assertEquals(2, count($generatedResource->arrayOfDateProp));
        $this->assertInstanceOf('\DateTime', $generatedResource->arrayOfDateProp[0]);
        $this->assertInstanceOf('\DateTime', $generatedResource->arrayOfDateProp[1]);


        //print_r(json_encode($personCertificates->serialize()));

        return $generatedResource;
    }

    /*
    public function testQueryMultiple()
    {
        $byrd = new PersonCertificates('01105914');
        $sen = new PersonCertificates('00096580');

        PersonCertificates::query(array($byrd, $sen));

        // Test attributes
        $this->assertEquals(true, $byrd->PIE);
        $this->assertEquals(true, $sen->PIE);

        $this->assertEquals('Expired', $byrd->animalCare->status);
        $this->assertEquals('Expired', $sen->animalCare->status);

        $this->assertEquals('Auto-Complete', $byrd->OHR->workflow);
        $this->assertEquals('Complete', $sen->OHR->workflow);
    }*/

    /**
     * Theoretically, this test SHOULD fail since a bad ID shouldn't
     * give us good results. But the backend stored procedures make no
     * differentiation between what a good ID vs a bad ID is to be able
     * to support that on the API end. 
     */
    /*public function testQueryInvalid()
    {
        $invalid = new PersonCertificates('B4DF00D');

        PersonCertificates::query(array($invalid));

        //print_r($invalid->serialize());

        $this->assertEquals('B4DF00D', $invalid->id());
        $this->assertEquals('/person/B4DF00D/certificates', $invalid->meta->uri);

        $this->assertEquals(false, $invalid->PIE);
    }
    */
}
