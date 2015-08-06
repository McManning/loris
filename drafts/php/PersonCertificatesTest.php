<?php

use Loris\Discovery;
use Loris\Utility;

use Loris\Resource\Person;
use Loris\Resource\PersonCertificates;

class PersonCertificatesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Perform model registration for Discovery
     */
    protected function setUp()
    {
        Discovery::register(
            Person::URI, '\\Loris\\Resource\\Person'
        );
        Discovery::register(
            PersonCertificates::URI, '\\Loris\\Resource\\PersonCertificates'
        );
    }


    /**
     * Create a new instance of a PersonCertificates resource.
     *
     * @return PersonCertificates
     */
    public function testCreate()
    {
        $personCertificates = new PersonCertificates('01105914');

        // Test metadata
        $this->assertEquals('01105914', $personCertificates->id());
        $this->assertEquals('/person/01105914/certificates', $personCertificates->meta->uri);

        return $personCertificates;
    }

    /**
     * Query for a specific PersonCertificates resource.
     *
     * @param PersonCertificates $personCertificates
     *
     * @depends testCreate
     *
     * @return PersonCertificates
     */
    public function testQuery(PersonCertificates $personCertificates)
    {
        PersonCertificates::query(array($personCertificates));

        // Test metadata
        $this->assertEquals('01105914', $personCertificates->id());
        $this->assertEquals('/person/01105914/certificates', $personCertificates->meta->uri);

        // Test simple attributes
        $this->assertEquals(true, $personCertificates->PIE);

        //print_r(json_encode($personCertificates->serialize()));

        return $personCertificates;
    }

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
    }

    /**
     * Theoretically, this test SHOULD fail since a bad ID shouldn't
     * give us good results. But the backend stored procedures make no
     * differentiation between what a good ID vs a bad ID is to be able
     * to support that on the API end. 
     */
    public function testQueryInvalid()
    {
        $invalid = new PersonCertificates('B4DF00D');

        PersonCertificates::query(array($invalid));

        //print_r($invalid->serialize());

        $this->assertEquals('B4DF00D', $invalid->id());
        $this->assertEquals('/person/B4DF00D/certificates', $invalid->meta->uri);

        $this->assertEquals(false, $invalid->PIE);
    }

}
