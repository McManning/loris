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

    protected $expansions = null;

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
    public function fromResults(array $results)
    {
        /*
            Expect:
            array(
                id,
                personsId,
                personsTotal,
                departmentsId,
                departmentsTotal
            )
        */

        // Update id(), as we may have potentially not had it pre-query
        $this->id($results['id']);

        // Hydrate attributes
        // NONE

        // Hydrate relationships
        if ($results['personsId'] !== null) {
            $this->persons->id($results['personsId']);
            $this->persons->meta->total = $results['personsTotal'];
        } else {
            // Data source tells us there is no associated PersonCollection
            $this->persons = new NullResource();
        }

        if ($results['departmentsId'] !== null) {
            $this->departments->id($results['departmentsId']);
            $this->departments->meta->total = $results['departmentsTotal'];
        } else {
            // Data source tells us there is no associated DepartmentCollection
            $this->departments = new NullResource();
        }

        $this->doExpansions();
    }

    /**
     * @todo generator pattern
     *
     * @param array $resources
     */
    public function expand(array $resources)
    {
        $this->expansions = $resources;
    }

    /**
     * Perform actual expansions after hydration, in case we dynamically
     * add additional resource references while hydrating from the data store
     * (e.g. resources stored in Arrays or Objects)
     */
    private function doExpansions()
    {
        if ($this->expansions === null) {
            return;
        }
        
        // Collection relationship
        if (array_key_exists('persons', $this->expansions)) {
            $this->persons = new \Loris\Resource\PersonCollection(
                $this->persons->id()
            );

            if (is_array($this->expansions['persons'])) {
                $this->persons->expand($this->expansions['persons']);
            }
        }

        // Collection relationship
        if (array_key_exists('departments', $this->expansions)) {
            $this->departments = new \Loris\Resource\DepartmentCollection(
                $this->departments->id()
            );

            if (is_array($this->expansions['departments'])) {
                $this->departments->expand($this->expansions['departments']);
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

