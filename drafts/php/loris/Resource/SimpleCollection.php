<?php
namespace Loris\Resource;

use \Loris\Utility;

class SimpleCollection extends Base\SimpleCollection
{
    public static function query(array $simpleCollections)
    {
        $results = array();

        foreach ($simpleCollections as $simpleCollection) {
            $results[] = $simpleCollection->querySingle();
        }

        self::postQuery($simpleCollections, $results);
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
                    id, page, limit, total

                rowset 1 (ids of each resource on the page)
                    id, simpleResourceId, 
                    ...

            Useful variables for your query include:
                $this->ids() - unique identifiers of this collection
                $this->meta->page - requested collection page
                $this->meta->limit - requested number of results 

            The output of your query must be formatted as below:

            $results = \stdClass(
                id = string
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
