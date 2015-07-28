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
     * @return array
     */
    public static function reformatSqlResults($statement)
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

                // If we somehow got a row with an ID that wasn't in our first rowset
                // (thus not a valid resource id) throw an error.
                if (!array_key_exists($id, $reformatted)) {
                    throw new \Exception('ID ['.$id.'] does not exist in first rowset');
                }
                
                array_push($reformatted[$id][$rowsetIndex], $row);
            }
        }

        return $reformatted;
    }
}
