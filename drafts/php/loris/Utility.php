<?php
namespace Loris;

/**
 * Commonly used utility methods.
 */
class Utility 
{
    /**
     * Generates an array of all unique IDs from the input resources.
     *
     * @param array $resources that each have an `id` attribute 
     * 
     * @return array
     */
    public static function getIds(array $resources) 
    {
        $ids = array();

        foreach ($resources as $resource) {
            $ids[$resource->id()] = true;
        }

        return array_keys($ids);
    }

    /**
     * Takes rowsets from an executed SQL statement and rebuilds them
     * in a way that each row of each rowset is associated with some
     * top level `id`. 
     *
     * @param PDOStatement $statement
     * @param array $complexAttributes
     *
     * @return array
     */
    public static function parseSqlResults($statement, $complexAttributes = null)
    { 
        $reformatted = array();
        $rowsetIndex = 0;

        // Grab first rowset
        $rowset = $statement->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rowset as $row) {
            $id = $row['id'];

            // Add an ID entry for each row, if it doesn't exist
            if (!array_key_exists($id, $reformatted)) {
                $reformatted[$id] = array(array());
            }

            // Add this row to the first rowset of $reformatted[id]
            array_push($reformatted[$id][$rowsetIndex], $row);
        }

        //print('-----');
        //print_r($rowset);

        while ($statement->nextRowset()) {
            $rowsetIndex++;
            $rowset = $statement->fetchAll(\PDO::FETCH_ASSOC);

            //print('---'.$rowsetIndex.'---');
            //print_r($rowset);

            // If we have a new rowset, add a container array() 
            // entry for each unique ID in $reformatted
            foreach ($reformatted as $id => $rowsets) {
                array_push($reformatted[$id], array());
            }

            //print('---reformat----');
            //print_r($reformatted);

            // For each row, copy to their appropriate rowset under
            // the correct `id` in $reformatted
            foreach ($rowset as $row) {
                $id = $row['id'];

                // Scrub out ID attribute as we don't want it in the result set
                unset($row['id']);

                // If we somehow got a row with an ID that wasn't in our first rowset
                // (thus not a valid resource id) throw an error.
                if (!array_key_exists($id, $reformatted)) {
                    throw new \Exception('ID ['.$id.'] does not exist in first rowset');
                }
                
                array_push($reformatted[$id][$rowsetIndex], $row);
            }
        }

        // Now shuffle things around a bit to be even more object-oriented
        $results = array();
        foreach ($reformatted as $id => $rowsets) {
            // Main attributes are in the first row of the first rowset
            $results[$id] = (object)$rowsets[0][0];

            // Look for any complex attributes we need to append
            if ($complexAttributes) {
                foreach ($complexAttributes as $attr => $properties) {

                    // Do some quick validation to ensure we even have
                    // the desired rowset in our results 
                    if (count($reformatted[$id]) <= $properties['rowset']) {
                        throw new \Exception(
                            'Rowset for attribute [' . $attr . '] does not exist'
                        );
                    }

                    $rows = &$reformatted[$id][$properties['rowset']];

                    if ($properties['type'] === 'array') {
                        $results[$id]->{$attr} = array();

                        if (array_key_exists('column', $properties)) {
                            // If a column was specified, this is going to be an array
                            // of just that column's values
                            $column = $properties['column'];

                            if (!array_key_exists($column, $rows[0])) {
                                throw new \Exception(
                                    'Single column [' . $column . '] for attribute [' 
                                    . $attr . '] does not exist'
                                );
                            }

                            foreach ($rows as $row) {
                                array_push($results[$id]->{$attr}, $row[$column]);
                            }
                        } else {
                            // If the complex attribute is an array, use all rows from
                            // the desired rowset as our attribute value.
                            $results[$id]->{$attr} = array();
                            foreach ($rows as $row) {
                                array_push($results[$id]->{$attr}, (object)$row);
                            }
                        }
                        
                    } elseif ($properties['type'] === 'object') {

                        // Complex attribute is an object, so use only the first row
                        $results[$id]->{$attr} = (object)$rows[0];

                    } else {
                        // We have no idea what this is. Error out. 
                        throw new \Exception(
                            'Unknown attribute type [' . $properties['type'] . ']'
                        );
                    }
                }
            }
        }

        return $results;
    }

    public static function isString($object, $property = null)
    {
        if ($object instanceof \stdClass) {
            if (!property_exists($object, $property)) {
                return false;
            }

            return $object->{$property} === null ||
                is_string($object->{$property});
        } else {
            return $object === null ||
                is_string($object);
        }
    }

    public static function isDate($object, $property = null)
    {
        // TODO: Date checks
        return self::isString($object, $property); 
    }

    public static function isBool($object, $property = null)
    {
        if ($object instanceof \stdClass) {
            if (!property_exists($object, $property)) {
                return false;
            }

            if (is_string($object->{$property})) {
                return in_array(strtolower($object->{$property}),
                    array('false', 'off', '-', 'no', 'n', '0', '',
                        'true', 'on', '+', 'yes', 'y', '1')
                );
            } else {
                return $object->{$property} === null || 
                    is_bool($object->{$property});
            }
        } else {
            if (is_string($object)) {
                return in_array(strtolower($object),
                    array('false', 'off', '-', 'no', 'n', '0', '',
                        'true', 'on', '+', 'yes', 'y', '1')
                );
            } else {
                return $object === null || 
                    is_bool($object);
            }
        }
    }

    public static function isNumber($object, $property = null)
    {
        if ($object instanceof \stdClass) {
            if (!property_exists($object, $property)) {
                return false;
            }

            return $object->{$property} === null || 
                is_numeric($object->{$property});
        } else {
            return $object === null ||
                is_numeric($object);
        }
    }
}
