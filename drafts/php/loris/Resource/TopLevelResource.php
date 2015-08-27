<?php
namespace Loris\Resource;

use \Loris\Utility;

class TopLevelResource extends Base\TopLevelResource
{
    /**
     * @param array(TopLevelResource) $topLevelResources
     */
    public static function query(array $topLevelResources)
    {
        /**********************************************************
            YOUR BACKEND QUERY CODE HERE
            The output of your query must be formatted as below:

            $results = \stdClass(
                    stringProp = string
                    topLevelCollectionPropTotal = number
                ...
            )

            !!  Important note: It may be possible that multiple
                resources that share the same ID may be in the list
                at once depending on how expansions and relationships
                are performed internally.

        ***********************************************************/

        self::postQuery($topLevelResources, $results);
    }
}
