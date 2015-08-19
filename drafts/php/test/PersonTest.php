<?php

use Loris\Discovery;
use Loris\Utility;

use Loris\Resource\Person;
use Loris\Resource\Department; // Person dependency

class PersonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Perform model registration for Discovery
     */
    protected function setUp()
    {
        Discovery::register(
            Person::URI, '\\Loris\\Resource\\Person'
        );

        // Dependency for Person
        Discovery::register(
            Department::URI, '\\Loris\\Resource\\Department'
        );
    }

    /**
     * Create a new instance of a Person resource.
     *
     * @return Person
     */
    public function testCreate()
    {
        $person = new Person(array('id' => '200275154'));

        // Test metadata
        $this->assertEquals(array('id' => '200275154'), $person->ids());
        $this->assertSame('/person/200275154', $person->meta->uri);

        return $person;
    }

    /**
     * Query for a specific Person resource.
     *
     * @param Person $person
     *
     * @depends testCreate
     *
     * @return Person
     */
    public function testQuery(Person $person)
    {
        Person::query(array($person));

        // Test metadata
        $this->assertSame(array('id' => '200275154'), $person->ids());
        $this->assertSame('/person/200275154', $person->meta->uri);

        // Test simple attributes
        $this->assertSame('Richard', $person->firstName);
        $this->assertSame('McManning', $person->lastName);
        $this->assertSame('mcmanning.1', $person->username);

        // Test complex attributes
        $this->assertSame(1, count($person->addresses));
        $this->assertSame('1960 Kenny Rd', $person->addresses[0]->address1);
        $this->assertSame('Columbus', $person->addresses[0]->city);
        $this->assertSame('OH', $person->addresses[0]->state);
        $this->assertSame('43210', $person->addresses[0]->zip);
        $this->assertSame('Research Foundation Building', $person->addresses[0]->building);
        $this->assertSame('6143309689', $person->addresses[0]->phone);

        // Ensure relationships are of Meta(Collection) and with proper metadata
        $this->assertInstanceOf('Loris\\Resource\\Base\\Meta', $person->department);
        $this->assertSame('/department/40180', $person->department->meta->uri);

        return $person;
    }

    /**
     * Test if the generated JSON matches expectations.
     * We use crc32() to speed/clean things up, rather than a straight string compare.
     * 
     * @depends testQuery
     */
    public function testSerialize(Person $person)
    {
        $serialized = $person->serialize();

        $json = json_encode($serialized, JSON_PRETTY_PRINT);
        $f = fopen('person.json', 'w');
        fwrite($f, $json);
        fclose($f);
        $this->assertEquals(2602382533, crc32($json));
    }

    /**
     * Test expanding a Person's primary Department
     */
    public function testExpandDepartment()
    {
        $person = new Person(array('id' => '200275154'));

        // Test metadata
        $this->assertSame(array('id' => '200275154'), $person->ids());
        $this->assertSame('/person/200275154', $person->meta->uri);

        $person->expand(array(
            'department' => true
        ));

        Person::query(array($person));

        // Test metadata
        $this->assertSame(
            array('id' => '40180'),
            $person->department->ids()
        );

        $this->assertSame(
            '/department/40180',
            $person->department->meta->uri
        );

        // Pick a random property to test (should exist since we expanded)
        $this->assertEquals(
            '40180', 
            $person->department->title
        );

        $serialized = $person->serialize();

        // TODO: Better assertions. I just used a visual test against the
        // generated JSON, but we need to automate this better. 
        $json = json_encode($serialized, JSON_PRETTY_PRINT);
        $f = fopen('person-department.json', 'w');
        fwrite($f, $json);
        fclose($f);

        // Test serialization results
        $this->assertEquals(1763362420, crc32($json));
    }

}
