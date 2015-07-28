<?php
namespace Loris\Resource;

use \Loris\Utility;

class ExampleCollection extends Base\ExampleCollection
{
    public static function query(array $collections)
    {
        $results = array();

        // Execute querySingle() for each collection, and 
        // merge the results together as one results set
        foreach ($collections as $collection) {
            // Yes, + is overloaded to union arrays in PHP.
            $results += $collection->querySingle();
        }

        self::postQuery($collections, $results);
    }

    /**
     * @brief Simpler query for a set of collection results.
     * 
     * Since we do not currently have the capability to query multiple
     * collections simultaneously (due to the need for each to pass in its
     * own page & limit), querySingle() runs a single instance of a collection,
     * and then returns the results to be merged in with the rest. 
     * 
     * @return array
     */
    public function querySingle()
    {
        //try {
            $db = new \PDO(YOUR_DSN, YOUR_USER, YOUR_PASS);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Note the below is for MSSQL
            $statement = $db->prepare('
                -- Rowset 1, Collection metadata
                SELECT
                    id = :id,
                    page = :page,
                    limit = :limit,
                    total = (SELECT COUNT(user_id) FROM users);

                -- Rowset 2, Person IDs
                SELECT      id = :id,
                            RTRIM(user_id) AS resourceId
                FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY user_id) AS RowNum,
                            user_id
                    FROM    users
                ) AS RowConstrainedResults
                WHERE       RowNum > ((:page - 1) * :limit)
                AND         RowNum <= (:page * :limit)
                ORDER BY     RowNum;
            ');

            $id = $this->id();
            $statement->bindParam(':id', $id);
            $statement->bindParam(':page', $this->meta->page, \PDO::PARAM_INT);
            $statement->bindParam(':limit', $this->meta->limit, \PDO::PARAM_INT);

            $statement->execute();

            $results = Utility::parseSqlResults($statement);
            return $results;
        //} catch(PDOException $e) {
        //   echo 'ERROR: ' . $e->getMessage();
        //}
    }

}
