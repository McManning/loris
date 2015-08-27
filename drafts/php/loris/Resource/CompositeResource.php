<?php
namespace Loris\Resource;

use \Loris\Utility;

class CompositeResource extends Base\CompositeResource
{
    /**
     * @param array(CompositeResource) $compositeResources
     */
    public static function query(array $compositeResources)
    {
        /**********************************************************
            YOUR BACKEND QUERY CODE HERE
            Useful variables for your query include:

                $compositeResources[N]->ids()
                - A mapping between primary keys and their values. 
                    For this resource type, it looks like:
                    array(
                        idLeft = string
                        idRight = string
                    )
            The output of your query must be formatted as below:

            $results = array(
                \stdClass(
                    idLeft = string
                    idRight = string
                )
                ...
            )

            !!  Important note: It may be possible that multiple
                resources that share the same ID may be in the list
                at once depending on how expansions and relationships
                are performed internally.

        ***********************************************************/

        self::postQuery($compositeResources, $results);
    }
}
