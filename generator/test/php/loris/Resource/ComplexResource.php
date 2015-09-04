<?php
namespace Loris\Resource;

use \Loris\Utility;

class ComplexResource extends Base\ComplexResource
{
    /**
     * @param array(ComplexResource) $complexResources
     */
    public static function query(array $complexResources)
    {
        /**********************************************************
            YOUR BACKEND QUERY CODE HERE
            Useful variables for your query include:

                $complexResources[N]->ids()
                - A mapping between primary keys and their values. 
                    For this resource type, it looks like:
                    array(
                        idLeft = string
                        idRight = string
                    )
            The output of your query must be formatted as below:

            $results = array(
                \stdClass(
                    arrayOfCollectionProp = array(
                        stdClass(
                            simpleCollectionId = string
                            simpleCollectionTotal = number
                        )
                        ...
                    )
                    arrayOfCompositeCollectionProp = array(
                        stdClass(
                            compositeCollectionIdLeft = string
                            compositeCollectionIdRight = string
                            compositeCollectionTotal = number
                        )
                        ...
                    )
                    arrayOfCompositeResourceProp = array(
                        stdClass(
                            compositeResourceIdLeft = string
                            compositeResourceIdRight = string
                        )
                        ...
                    )
                    arrayOfDateProp = array(
                        string formatted 'Y-m-d'
                        ...
                    )
                    arrayOfNumberProp = array(
                        number
                        ...
                    )
                    arrayOfObjectProp = array(
                        stdClass(
                            aopBoolProp = boolean
                            aopCollectionPropId = string
                            aopCollectionPropTotal = number
                            aopCompositeCollectionPropIdLeft = string
                            aopCompositeCollectionPropIdRight = string
                            aopCompositeCollectionPropTotal = number
                            aopCompositeResourcePropIdLeft = string
                            aopCompositeResourcePropIdRight = string
                            aopDateProp = string formatted 'Y-m-d'
                            aopNumberProp = number
                            aopResourcePropId = string
                            aopStringProp = string
                        )
                        ...
                    )
                    arrayOfResourceProp = array(
                        stdClass(
                            simpleResourceId = string
                        )
                        ...
                    )
                    arrayOfStringProp = array(
                        string
                        ...
                    )
                    boolProp = boolean
                    collectionPropId = string
                    collectionPropTotal = number
                    compositeCollectionPropIdLeft = string
                    compositeCollectionPropIdRight = string
                    compositeCollectionPropTotal = number
                    compositeResourcePropIdLeft = string
                    compositeResourcePropIdRight = string
                    dateProp = string formatted 'Y-m-d'
                    idLeft = string
                    idRight = string
                    numberProp = number
                    objectProp = \stdClass(
                        opBoolProp = boolean
                        opCollectionPropId = string
                        opCollectionPropTotal = number
                        opCompositeCollectionPropIdLeft = string
                        opCompositeCollectionPropIdRight = string
                        opCompositeCollectionPropTotal = number
                        opCompositeResourcePropIdLeft = string
                        opCompositeResourcePropIdRight = string
                        opDateProp = string formatted 'Y-m-d'
                        opNumberProp = number
                        opResourcePropId = string
                        opStringProp = string
                    )
                    resourcePropId = string
                    stringProp = string
                )
                ...
            )

            !!  Important note: It may be possible that multiple
                resources that share the same ID may be in the list
                at once depending on how expansions and relationships
                are performed internally.

        ***********************************************************/

        self::postQuery($complexResources, $results);
    }
}
