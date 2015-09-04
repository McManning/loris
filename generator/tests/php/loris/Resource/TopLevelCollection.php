<?php
namespace Loris\Resource;

use \Loris\Utility;

class TopLevelCollection extends Base\TopLevelCollection
{
    public static function query(array $topLevelCollections)
    {
        $results = array();

        foreach ($topLevelCollections as $topLevelCollection) {
            $results[] = $topLevelCollection->querySingle();
        }

        self::postQuery($topLevelCollections, $results);
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
        /**********************************************************
            YOUR BACKEND QUERY CODE HERE

            For SQL/PDO, it expects the following:

                rowset 0 (collection attributes)
                    page, limit, total

                rowset 1 (ids of each resource on the page)
                    simpleResourceId, 
                    ...

            Useful variables for your query include:
                $this->meta->page - requested collection page
                $this->meta->limit - requested number of results 

            The output of your query must be formatted as below:

            $results = \stdClass(
                page = number
                limit = number
                total = number
                collection = array(
                    \stdClass(
                        simpleResourceId
                    )
                )
            )

            The below code assumes $statement is a PDO  
            prepared statement that has been executed. 
        ***********************************************************/

        $results = Utility::parseSqlResults(
            $statement,
            array(
                'collection' => array( // Array of objects containing resource ids
                    'rowset' => 1,
                    'type' => 'array'
                )
            )
        );

        return $results;
    }
}
