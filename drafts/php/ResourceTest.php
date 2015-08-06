<?php

use Loris\Discovery;
use Loris\Utility;

use Loris\Resource\Person;
use Loris\Resource\PersonCollection;
use Loris\Resource\Department;
use Loris\Resource\PersonCoworkers;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Perform model registration for Discovery
     *
     * @todo Obviously the Discovery unit tests are defunct
     * at this point (because we're pushing as a setup, but 
     * wanting to TEST push) so they need to be removed from
     * this test class and put in their own. 
     */
    protected function setUp()
    {
        Discovery::register(
            Person::URI, '\\Loris\\Resource\\Person'
        );
        Discovery::register(
            PersonCollection::URI, '\\Loris\\Resource\\PersonCollection'
        );
        Discovery::register(
            PersonCoworkers::URI, '\\Loris\\Resource\\PersonCoworkers'
        );
        Discovery::register(
            Department::URI, '\\Loris\\Resource\\Department'
        );
    }

    /**
     * Create a new instance of a Person resource.
     *
     * @return Person
     */
    public function testCreatePerson()
    {
        $person = new Person('200275154');

        // Test metadata
        $this->assertEquals('200275154', $person->id());
        $this->assertEquals('/person/200275154', $person->meta->uri);

        return $person;
    }

    /**
     * Query for a specific Person resource.
     *
     * @param Person $person
     *
     * @depends testCreatePerson
     *
     * @return Person
     */
    public function testQueryPerson(Person $person)
    {
        Person::query(array($person));

        // Test metadata
        $this->assertEquals('200275154', $person->id());
        $this->assertEquals('/person/200275154', $person->meta->uri);

        // Test simple attributes
        $this->assertEquals('Richard', $person->firstName);
        $this->assertEquals('McManning', $person->lastName);
        $this->assertEquals('mcmanning.1', $person->username);

        return $person;
    }

    /**
     * Test complex attributes (objects, arrays) that were hydrated.
     *
     * @param Person $person
     *
     * @depends testQueryPerson
     */
    public function testPersonComplexAttributes(Person $person)
    {
        // Test complex attributes
        $this->assertEquals(1, count($person->addresses));
        $this->assertEquals('1960 Kenny Rd', $person->addresses[0]->address1);
        $this->assertEquals('Columbus', $person->addresses[0]->city);
        $this->assertEquals('OH', $person->addresses[0]->state);
        $this->assertEquals('43210', $person->addresses[0]->zip);
        $this->assertEquals('Research Foundation Building', $person->addresses[0]->building);
        $this->assertEquals('6143309689', $person->addresses[0]->phone);
    }

    /**
     * Test the data type of non-expanded relationships of Person.
     *
     * @param Person $person
     *
     * @depends testQueryPerson
     */
    public function testPersonRelationships(Person $person)
    {
        // Ensure relationships are of Meta(Collection) and with proper metadata
        $this->assertInstanceOf('Loris\\Resource\\Base\\Meta', $person->department);
        $this->assertEquals('/department/40180', $person->department->meta->uri);

        $this->assertInstanceOf('Loris\\Resource\\Base\\MetaCollection', $person->coworkers);
        $this->assertEquals('/person/{id}/coworkers', $person->coworkers->uri());
        $this->assertEquals('/person/200275154/coworkers', $person->coworkers->meta->uri);
    }

    /**
     * Create a new instance of a Department resource.
     *
     * @return Department
     */
    public function testCreateDepartment()
    {
        $department = new Department('40000');

        // Test metadata
        $this->assertEquals('40000', $department->id());
        $this->assertEquals('/department/40000', $department->meta->uri);

        return $department;
    }

    /**
     * Query a specific Department resource
     *
     * @param Department $department
     *
     * @depends testCreateDepartment
     */
    public function testQueryDepartment($department)
    {
        Department::query(array($department));

        // Test metadata
        $this->assertEquals('40000', $department->id());
        $this->assertEquals('/department/40000', $department->meta->uri);

        // Test attributes
        $this->assertEquals('Research Administration', $department->title);
        $this->assertEquals('Res Admin', $department->shortTitle);
        $this->assertEquals('Bricker Hall', $department->building);
        $this->assertEquals('190 N Oval Mall', $department->address);
    }

    /**
     * Retrieve multiple Person resources simultaneously.
     *
     * @depends testQueryPerson
     */
    public function testQueryPersonsList()
    {
        $chase = new Person('200275154');
        $john = new Person('93111472');
        Person::query(array($chase, $john));

        // Test metadata
        $this->assertEquals('200275154', $chase->id());
        $this->assertEquals('/person/200275154', $chase->meta->uri);

        $this->assertEquals('93111472', $john->id());
        $this->assertEquals('/person/93111472', $john->meta->uri);

        // Test simple attributes
        $this->assertEquals('Richard', $chase->firstName);
        $this->assertEquals('McManning', $chase->lastName);
        $this->assertEquals('mcmanning.1', $chase->username);

        $this->assertEquals('John', $john->firstName);
        $this->assertEquals('Ray', $john->lastName);
        $this->assertEquals('ray.30', $john->username);
    }

    /**
     * Expand Department relationship for a Person
     */
    public function testQueryPersonWithDepartment()
    {
        $person = new Person('200275154');
        $person->expand(array(
            'department' => true
        ));

        Person::query(array($person));

        // Test Person metadata
        $this->assertEquals('200275154', $person->id());
        $this->assertEquals('/person/200275154', $person->meta->uri);

        // Test simple attributes
        $this->assertEquals('Richard', $person->firstName);
        $this->assertEquals('McManning', $person->lastName);
        $this->assertEquals('mcmanning.1', $person->username);

        // Test relationship
        $this->assertInstanceOf('Loris\\Resource\\Department', $person->department);

        // Test Department metadata
        $this->assertEquals('40180', $person->department->id());
        $this->assertEquals('/department/40180', $person->department->meta->uri);

        // Test Department attributes
        $this->assertEquals('40180', $person->department->title); // :( our dept has incomplete data.
        $this->assertEquals(null, $person->department->address);
        $this->assertEquals(null, $person->department->building);
    }

    /**
     * Expand PersonCoworkers relationship for a Person
     */
    public function testQueryPersonWithCoworkers()
    {
        $person = new Person('200275154');
        $person->expand(array(
            'coworkers' => true
        ));

        Person::query(array($person));

        // Test Person metadata
        $this->assertEquals('200275154', $person->id());
        $this->assertEquals('/person/200275154', $person->meta->uri);

        // Test simple attributes
        $this->assertEquals('Richard', $person->firstName);
        $this->assertEquals('McManning', $person->lastName);
        $this->assertEquals('mcmanning.1', $person->username);

        // Test relationship
        $this->assertInstanceOf('Loris\\Resource\\PersonCoworkers', $person->coworkers);

        // Test PersonCoworkers metadata
        $this->assertEquals('200275154', $person->coworkers->id());
        $this->assertEquals('/person/200275154/coworkers', $person->coworkers->meta->uri);
        $this->assertEquals(37, $person->coworkers->meta->total);
        $this->assertEquals(10, $person->coworkers->meta->limit);
        $this->assertEquals(1, $person->coworkers->meta->page);
    }


    /**
     * Expand Department relationship nested within an array
     * of objects for a Person. 
     */
    public function testQueryPersonWithNestedDepartment()
    {
        $person = new Person('200275154');
        $person->expand(array(
            'otherDepartments' => array(
                'department' => true
            )
        ));

        Person::query(array($person));

        // Test Person metadata
        $this->assertEquals('200275154', $person->id());
        $this->assertEquals('/person/200275154', $person->meta->uri);

        // Test simple attributes
        $this->assertEquals('Richard', $person->firstName);
        $this->assertEquals('McManning', $person->lastName);
        $this->assertEquals('mcmanning.1', $person->username);

        // Test array
        $this->assertEquals(2, count($person->otherDepartments));

        // Test relationship
        $firstOtherDepartment = $person->otherDepartments[0];
        $secondOtherDepartment = $person->otherDepartments[1];
        $this->assertInstanceOf('Loris\\Resource\\Department', $firstOtherDepartment->department);
        $this->assertInstanceOf('Loris\\Resource\\Department', $secondOtherDepartment->department);

        // Test Department metadata
        $this->assertEquals('40000', $firstOtherDepartment->department->id());
        $this->assertEquals('/department/40000', $firstOtherDepartment->department->meta->uri);

        $this->assertEquals('14350', $secondOtherDepartment->department->id());
        $this->assertEquals('/department/14350', $secondOtherDepartment->department->meta->uri);

        // Test Department attributes
        $this->assertEquals('Research Administration', $firstOtherDepartment->department->title);
        $this->assertEquals('190 N Oval Mall', $firstOtherDepartment->department->address);
        $this->assertEquals('Bricker Hall', $firstOtherDepartment->department->building);

        $this->assertEquals('Computer Science & Engineering', $secondOtherDepartment->department->title);
        $this->assertEquals('2015 Neil Ave', $secondOtherDepartment->department->address);
        $this->assertEquals('Dreese Laboratories', $secondOtherDepartment->department->building);

        return $person;
    }

    /**
     * Ensure serialization works for complex arrays with objects containing resources.
     * (That's a mouthful...)
     *
     * @depends testQueryPersonWithNestedDepartment
     */
    public function testSerializePersonWithNestedDepartment(Person $person)
    {
        $serialized = $person->serialize();

        $json = json_encode($serialized);
        $f = fopen('person_with_nested_department.json', 'w');
        fwrite($f, $json);
        fclose($f);
        $this->assertEquals(2086464857, crc32($json));
    }

    /**
     * Query for all known Persons as a PersonCollection
     */
    public function testQueryPersonCollection()
    {
        $collection = new PersonCollection(
            null
        );

        PersonCollection::query(array($collection));

        // Test metadata
        $this->assertEquals('/person', $collection->meta->uri);
        $this->assertEquals(69057, $collection->meta->total);

        // Check collection size
        $this->assertEquals(10, count($collection->collection));

        // Check collection item type
        $this->assertInstanceOf('Loris\\Resource\\Person', $collection->collection[0]);
    }

    /**
     * Test if the generated JSON matches expectations.
     * We use crc32() to speed/clean things up, rather than a straight string compare.
     * 
     * @depends testQueryPerson
     */
    public function testPersonSerialize(Person $person)
    {
        $serialized = $person->serialize();

        $json = json_encode($serialized);
        $f = fopen('person.json', 'w');
        fwrite($f, $json);
        fclose($f);
        $this->assertEquals(3929305707, crc32($json));
    }

    public function testDiscoveryRegister()
    {
        Discovery::register(
            Person::URI, 
            '\\Loris\\Resource\\Person'
        );
    }

    /**
     * @depends testDiscoveryRegister
     */
    public function testDiscoveryFindByUri()
    {
        $result = Discovery::find(Person::URI);

        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals('/person/{id}', $result->uri);
        $this->assertEquals('\\Loris\\Resource\\Person', $result->class);
    }

    /**
     * @depends testDiscoveryRegister
     */
    public function testDiscoveryFindByClassname()
    {
        $result = Discovery::find('\\Loris\\Resource\\Person');

        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals('/person/{id}', $result->uri);
        $this->assertEquals('\\Loris\\Resource\\Person', $result->class);
    }

    /**
     * Use Discovery to locate a registered resource and to create
     * a new instance of it (in this case, Person)
     *
     * @depends testDiscoveryRegister
     */
    public function testDiscoveryCreateResource()
    {
        $result = Discovery::find('\\Loris\\Resource\\Person');

        $person = new $result->class('200275154');

        $this->assertInstanceOf('Loris\\Resource\\Person', $person);
        $this->assertEquals('200275154', $person->id());
        $this->assertEquals('/person/200275154', $person->meta->uri);
    }

    public function testDiscoveryCreateCollection()
    {
        Discovery::register(
            PersonCollection::URI, 
            '\\Loris\\Resource\\PersonCollection'
        );

        $result = Discovery::find('\\Loris\\Resource\\PersonCollection');

        $collection = new $result->class();

        $this->assertInstanceOf('Loris\\Resource\\PersonCollection', $collection);
        $this->assertEquals('/person', $collection->meta->uri);
    }
    
}
