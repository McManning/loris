<?php
namespace Loris\Resource;

use \Loris\Utility;

class SimpleResource extends Base\SimpleResource
{
    /**
     * @param array(SimpleResource) $simpleResources
     */
    public static function query(array $simpleResources)
    {
        /**********************************************************
            YOUR BACKEND QUERY CODE HERE
            Useful variables for your query include:

                $simpleResources[N]->ids()
                - A mapping between primary keys and their values. 
                    For this resource type, it looks like:
                    array(
                        id = string
                    )
            The output of your query must be formatted as below:

            $results = array(
                \stdClass(
                    id = string
                )
                ...
            )

            !!  Important note: It may be possible that multiple
                resources that share the same ID may be in the list
                at once depending on how expansions and relationships
                are performed internally.

        ***********************************************************/

        self::postQuery($simpleResources, $results);
    }
}
