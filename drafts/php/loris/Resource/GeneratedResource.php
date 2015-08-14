<?php
namespace Loris\Resource;

use \Loris\Utility;

class GeneratedResource extends Base\GeneratedResource
{
    // Demo structured JSON data expected from a backend 
    const TEST_BACKEND_RESULT = <<<JSON
        {
            "id": "12345",
            "arrayOfCollectionProp": [
                {
                    "arrayCollectionId": "AC-12345",
                    "arrayCollectionTotal": 10
                }
            ],
            "arrayOfDateProp": [
                "2015-08-13",
                "2015-08-14"
            ],
            "arrayOfNumberProp": [
                1, 2, 3, 4, 5
            ],
            "arrayOfObjectProp": [
                {
                    "aopBoolProp": true,
                    "aopCollectionPropId": "AOPC-12345",
                    "aopCollectionPropTotal": 15,
                    "aopDateProp": "2015-12-31",
                    "aopNumberProp": 55,
                    "aopResourcePropId": "AOPR-12345",
                    "aopStringProp": "A string"
                }
            ],
            "arrayOfResourceProp": [
                {
                    "arrayResourceId": "AR-12345-1"
                },
                {
                    "arrayResourceId": "AR-12345-2"
                }
            ],
            "arrayOfStringProp": [
                "foo", "bar"
            ],
            "boolProp": false,
            "collectionPropId": "C-12345",
            "collectionPropTotal": 55,
            "dateProp": "1989-08-14",
            "numberProp": 14,
            "objectProp": {
                "opBoolProp": true,
                "opCollectionPropId": "OPC-12345",
                "opCollectionPropTotal": 13,
                "opDateProp": "2007-06-01",
                "opNumberProp": 12,
                "opResourcePropId": "OPR-12345",
                "opStringProp": "User is OP"
            },
            "resourcePropId": "R-12345",
            "stringProp": "Hello World"
        }
JSON;

    /**
     * @param array(GeneratedResource) $generatedResources
     */
    public static function query(array $generatedResources)
    {
        /**********************************************************
            YOUR BACKEND QUERY CODE HERE

            Useful variables for your query include:
                $generatedResources[N]->id() - unique identifier

            The output of your query must be formatted as below:

            $results = array(
                id = stdClass(
                    id = string
                    arrayOfCollectionProp = array(
                        stdClass(
                            arrayCollectionId
                            arrayCollectionTotal
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
                            aopDateProp = string formatted 'Y-m-d'
                            aopNumberProp = number
                            aopResourcePropId = string
                            aopStringProp = string
                        )
                        ...
                    )
                    arrayOfResourceProp = array(
                        stdClass(
                            arrayResourceId
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
                    dateProp = string formatted 'Y-m-d'
                    numberProp = number
                    objectProp = stdClass(
                        opBoolProp = boolean
                        opCollectionPropId = string
                        opCollectionPropTotal = number
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

        ***********************************************************/

        $results = array();

        foreach ($generatedResources as $resource) {
            $results[$resource->id()] = json_decode(self::TEST_BACKEND_RESULT);
            $results[$resource->id()]->id = $resource->id();
        }

        self::postQuery($generatedResources, $results);
    }
}
