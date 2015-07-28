<?php
namespace Loris\Resource\Base;

/**
 * @brief Example of a resource without an id.
 *
 * This `EndpointsRoot` resource simply points to other top level
 * resources and collections. This would also be an example
 * of discoverability for consumers.
 */
class EndpointsRoot extends Meta
{
    const URI = '/'; // Assigned to API root

    // Attributes
    // None

    // Relationships
    public $persons = null; // PersonCollection
    public $departments = null; // DepartmentCollection

    /**
     * @param string $id
     */
    function __construct($id = null)
    {
        parent::__construct($id, self::URI);

        $this->persons = new MetaCollection(
            null,
            '/person'
        );

        $this->departments = new MetaCollection(
            null,
            '/department'
        );
    }

    /**
     * @param array(EndpointsRoot) $endpoints
     */
    public static function query(array $endpointsRoots)
    {
        throw new \Exception(
            'Base\\EndpointsRoot::query() cannot be called directly.'
        );
    }

    public static function postQuery(array $endpointsRoots, array $results)
    {
        foreach ($endpointsRoots as $endpointsRoot) {
            if (!array_key_exists($endpointsRoot->id(), $results)) {
                throw new \Exception(
                    'EndpointsRoot [' . $endpointsRoot->id() . '] missing from $results'
                );
            }

            $endpointsRoot->fromRowsets($results[$endpointsRoot->id()]);
        }

        // Query for all expanded relationships
        $personCollections = array();
        $departmentCollections = array();

        foreach ($endpointsRoots as $endpointsRoot) {

            // Check for expanded PersonCollections
            if ($endpointsRoot->persons instanceof \Loris\Resource\PersonCollection) {
                array_push($personCollections, $endpointsRoot->persons);
            }

            // Check for expanded DepartmentCollections
            if ($endpointsRoot->department instanceof \Loris\Resource\Department) {
                array_push($departmentCollections, $endpointsRoot->departments);
            }
        }

        // If there are expanded PersonCollections, hydrate
        if (!empty($personCollections)) {
            \Loris\Resource\PersonCollection::query($personCollections);
        }

        // If there are expanded DepartmentCollections, hydrate
        if (!empty($departmentCollections)) {
            \Loris\Resource\DepartmentCollection::query($departmentCollections);
        }
    }

    /**
     * @todo generator pattern (?)
     *
     * @param array $rowsets
     */
    public function fromRowsets(array $rowsets)
    {
        /*
            Expect:
            array( // rowsets
                array( // rowset
                    array( // row
                        id
                    )
                )
            )
        */
        $attribsRow = $rowsets[0][0];

        // Update id(), as we may have potentially not had it pre-query
        $this->id($attribsRow['id']);

        // Hydrate attributes
        // NONE

        // Hydrate relationships
        if ($attribsRow['personsId'] !== null) {
            $this->persons->id($attribsRow['personsId']);
            $this->persons->meta->total = $attribsRow['personsTotal'];
        } else {
            // Data source tells us there is no associated PersonCollection
            $this->persons = new \Loris\Resource\Base\NullResource();
        }

        if ($attribsRow['departmentsId'] !== null) {
            $this->departments->id($attribsRow['departmentsId']);
            $this->departments->meta->total = $attribsRow['departmentsTotal'];
        } else {
            // Data source tells us there is no associated DepartmentCollection
            $this->departments = new \Loris\Resource\Base\NullResource();
        }
    }

    /**
     * @todo generator pattern
     *
     * @param array $resources
     */
    public function expand(array $resources)
    {
        // Collection relationship
        if (array_key_exists('persons', $resources)) {
            $this->persons = new \Loris\Resource\PersonCollection(
                $this->persons->id()
            );

            if (is_array($resources['persons'])) {
                $this->persons->expand($resources['persons']);
            }
        }

        // Collection relationship
        if (array_key_exists('departments', $resources)) {
            $this->departments = new \Loris\Resource\DepartmentCollection(
                $this->departments->id()
            );

            if (is_array($resources['departments'])) {
                $this->departments->expand($resources['departments']);
            }
        }
    }

    /**
     * @todo generator pattern
     *
     * @return stdClass
     */
    public function serialize()
    {
        // Get serialized data from Meta
        $serialized = parent::serialize();

        // Attributes
        // NONE

        // Relationships
        $serialized->persons = $this->persons->serialize();
        $serialized->departments = $this->departments->serialize();

        return $serialized;
    }
}

