<?php
namespace Loris\Resource;

use \Loris\Utility;

class ExampleResource extends Base\ExampleResource
{
    /**
     * @param array(ExampleResource) $exampleResources
     */
    public static function query(array $exampleResources)
    {
        //try {
            $db = new \PDO(YOUR_DSN, YOUR_USER, YOUR_PASS);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $ids = '\'' . implode('\',\'', Utility::getIds($exampleResources)) . '\'';

            $statement = $db->prepare("
                -- Rowset 0, User attributes
                SELECT      u.user_id AS id,
                            u.firstName,
                            u.lastName,
                            u.username,
                            u.department_id AS departmentId, 
                            u.id AS coworkersId, -- Same ID as ourselves
                            coworkersTotal = (
                                SELECT  COUNT(*) 
                                FROM    users
                                WHERE   department_id = u.department_id
                                AND     user_id != u.user_id
                            )
                FROM        users
                WHERE       u.user_id IN COMMA_LIST_TO_IDS(:ids);

                -- Rowset 1, associated addresses
                SELECT  a.user_id AS id,
                        a.address1,
                        a.address2,
                        a.city,
                        a.state,
                        a.zip
                FROM    user_addresses a
                WHERE   a.user_id IN COMMA_LIST_TO_IDS(:ids);
            ");

            $statement->bindParam(':ids', $ids);
            $statement->execute();

            $results = Utility::parseSqlResults(
                $statement,
                array(
                    'addresses' => array( // Array of objects
                        'rowset' => 1,
                        'type' => 'array'
                    )
                )
            );

            self::postQuery($exampleResources, $results);

        //} catch(\PDOException $e) {
        //    echo 'ERROR: ' . $e->getMessage();
        //}
    }
}


