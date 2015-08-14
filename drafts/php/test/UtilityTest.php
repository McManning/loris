<?php

use Loris\Utility;
use Loris\Resource\Person; // for Utility::getIds()

class UtilityTest extends \PHPUnit_Framework_TestCase
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
            '200275154' => (object)array(
                'id' => '200275154',
                'FirstName' => 'Richard',
                'LastName' => 'McManning'
            ),
            '93111472' => (object)array(
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
            '200275154' => (object)array(
                'id' => '200275154',
                'FirstName' => 'Richard',
                'LastName' => 'McManning',
                'addresses' => array(
                    (object)array(
                        'address1' => '1960 Kenny Rd',
                        'city' => 'Columbus'
                    )
                ),
                'job' => (object)array(
                    'department' => '40180',
                    'description' => 'Senior Full Stack Engineer'
                ),
                'emails' => array(
                    'mcmanning.1@osu.edu',
                    'mcmanning.1@research.osu.edu'
                )
            ),
            '93111472' => (object)array(
                'id' => '93111472',
                'FirstName' => 'John',
                'LastName' => 'Ray',
                'addresses' => array(
                    (object)array(
                        'address1' => '1960 Kenny Rd',
                        'city' => 'Columbus'
                    )
                ),
                'job' => (object)array(
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
}
