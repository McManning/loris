<?php

use Loris\Discovery;
use Loris\Utility;

use Loris\Resource\Person;
use Loris\Resource\PersonCollection;
use Loris\Resource\Department;
use Loris\Resource\PersonCoworkers;

class UnitTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIds()
    {
        $a = new Person('1');
        $b = new Person('2');

        $ids = Utility::getIds(array($a, $b));
        sort($ids);

        $this->assertEquals(array('1', '2'), $ids);
    }

    public function testGetIdsWithDuplicates()
    {
        $a = new Person('1');
        $b = new Person('2');

        $ids = Utility::getIds(array($a, $b, $a, $a));
        sort($ids);

        $this->assertEquals(array('1', '2'), $ids);
    }

    /**
     * Test a simple SELECT statement
     *
     */
    public function testMssqlSimple()
    {
        $db = new \PDO(RESUSER_DSN, RESUSER_USER, RESUSER_PASS);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $statement = $db->prepare("
            SELECT  RTRIM(FirstName) AS FirstName
            FROM    ResUser.dbo.ResName
            WHERE   ResID = '200275154';
        ");

        $statement->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // Close connection fully
        $statement->closeCursor();
        $statement = null;
        $db = null;

        $this->assertEquals(1, count($rows));
        $this->assertEquals('Richard', $rows[0]['FirstName']);
    }

    /**
     * Test doing multiple select statements in one query.
     *
     */
    public function testMssqlRowsets()
    {
        $db = new \PDO(RESUSER_DSN, RESUSER_USER, RESUSER_PASS);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $statement = $db->prepare("
            SELECT  RTRIM(FirstName) AS FirstName
            FROM    ResName
            WHERE   ResID = '200275154';

            SELECT  RTRIM(FirstName) AS FirstName
            FROM    ResName
            WHERE   ResID = '93111472';
        ");

        $statement->execute();
        $rowset1 = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $statement->nextRowset();
        $rowset2 = $statement->fetchAll(\PDO::FETCH_ASSOC);

        // Close connection fully
        $statement->closeCursor();
        $statement = null;
        $db = null;

        $this->assertEquals(1, count($rowset1));
        $this->assertEquals('Richard', $rowset1[0]['FirstName']);

        $this->assertEquals(1, count($rowset2));
        $this->assertEquals('John', $rowset2[0]['FirstName']);
    }

    public function testParseSqlResultsSimple()
    {
        $db = new \PDO(RESUSER_DSN, RESUSER_USER, RESUSER_PASS);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $statement = $db->prepare("
            SELECT  ResID AS id,
                    RTRIM(FirstName) AS FirstName,
                    RTRIM(LastName) AS LastName
            FROM    ResName
            WHERE   ResID IN ('200275154', '93111472');
        ");

        $statement->execute();

        $results = Utility::parseSqlResults($statement);

        // Close connection fully
        $statement->closeCursor();
        $statement = null;
        $db = null;

        $expected = array(
            '200275154' => array(
                'id' => '200275154',
                'FirstName' => 'Richard',
                'LastName' => 'McManning'
            ),
            '93111472' => array(
                'id' => '93111472',
                'FirstName' => 'John',
                'LastName' => 'Ray'
            )
        );

        $this->assertEquals($expected, $results);
    }

    /**
     * Ensure that Utility::parseSqlResults() can operate on 
     * queries with multiple rowsets and return expected array structures.
     */
    public function testParseSqlResultsComplex()
    {
        $db = new \PDO(RESUSER_DSN, RESUSER_USER, RESUSER_PASS);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $statement = $db->prepare("
            -- Rowset 0, attributes
            SELECT  ResID AS id,
                    RTRIM(FirstName) AS FirstName,
                    RTRIM(LastName) AS LastName
            FROM    ResName
            WHERE   ResID IN ('200275154', '93111472');

            -- Rowset 1, set address attribute to an array of matches
            SELECT  ResID AS id,
                    RTRIM(address1) AS address1,
                    RTRIM(city) AS city
            FROM    ResPlace 
            WHERE   ResID IN ('200275154', '93111472');

            -- Rowset 2, set job attribute to an object
            SELECT  ResID AS id,
                    RTRIM(TIU) AS department,
                    RTRIM(JobDesc) AS [description]
            FROM    ResJob 
            WHERE   ResID IN ('200275154', '93111472');

            -- Rowset 3, a flat array of just email addresses
            -- Ignore the dumb union, it's because these are actually 
            -- two columns in the same table, rather than being normalized.
            SELECT  ResID AS id,
                    EMail AS email
            FROM    ResUser.dbo.ResName
            WHERE   ResID IN ('200275154', '93111472')
            UNION
            SELECT  ResID AS id,
                    OSUeMail AS email
            FROM    ResUser.dbo.ResName 
            WHERE   ResID IN ('200275154', '93111472')
            AND     OSUeMail <> EMail;
        ");

        $statement->execute();

        $results = Utility::parseSqlResults(
            $statement, 
            array(
                // Array of objects
                'addresses' => array(
                    'rowset' => 1,
                    'type' => 'array'
                ),
                // Single object
                'job' => array(
                    'rowset' => 2,
                    'type' => 'object'
                ),
                // Array of single column values
                'emails' => array(
                    'rowset' => 3,
                    'type' => 'array',
                    'column' => 'email'
                )
            )
        );

        // Close connection fully
        $statement->closeCursor();
        $statement = null;
        $db = null;
        
        $expected = array(
            '200275154' => array(
                'id' => '200275154',
                'FirstName' => 'Richard',
                'LastName' => 'McManning',
                'addresses' => array(
                    array(
                        'address1' => '1960 Kenny Rd',
                        'city' => 'Columbus'
                    )
                ),
                'job' => array(
                    'department' => '40180',
                    'description' => 'Senior Full Stack Engineer'
                ),
                'emails' => array(
                    'mcmanning.1@osu.edu',
                    'mcmanning.1@research.osu.edu'
                )
            ),
            '93111472' => array(
                'id' => '93111472',
                'FirstName' => 'John',
                'LastName' => 'Ray',
                'addresses' => array(
                    array(
                        'address1' => '1960 Kenny Rd',
                        'city' => 'Columbus'
                    )
                ),
                'job' => array(
                    'department' => '40180',
                    'description' => 'Director-00'
                ),
                'emails' => array(
                    'ray.30@osu.edu',
                    'ray.30@research.osu.edu'
                )
            )
        );

        $this->assertEquals($expected, $results);
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
        $this->assertEquals(3427401585, crc32($json));
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
