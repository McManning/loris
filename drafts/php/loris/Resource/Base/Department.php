<?php
namespace Loris\Resource\Base;

use \Loris\Utility;

class Department extends Meta
{
    const URI = '/department/{id}';

    private $expansions = null;

    /**
     * Track the properties that are used as our resource's 
     * distinct identifier (one or composite). Each match
     * within URI must exist within this list. 
     */
    private $_id = array(
        'id'
    );

    // Properties

    /** type: string */
    public $address = null;

    /** type: string */
    public $building = null;

    /** type: string */
    public $id = null;

    /** type: string */
    public $shortTitle = null;

    /** type: string */
    public $title = null;

    /**
     * @param mixed $ids
     */
    function __construct($ids)
    {
        parent::__construct($ids, self::URI);

        // Create Meta(Collection)s for relationships
        // Note: The inclusion of a URI is required here as we can possibly
        // never replace Meta(Collection) with an actual class (if non-expanded)
        // but we still need to know the resource URI. The problem then becomes,
        // where do we get this URI? We can't guarantee to have access to Resource::URI
        // because resources may not exist on the same instance as this caller. 
        // Further note that ID keys must be specified prior to any access, as each
        // needs to know what to use as an ID. This is baked into resource implementations, 
        // but again, the resource may reside externally and we need to identify what,
        // from our data store on *this* resource, would be considered an ID attribute

    }

    /**
     *
     * @param array(Department) $departments
     */
    public static function query(array $departments)
    {
        throw new \Exception(
            'Base\\Department::query() cannot be called directly.'
        );
    }

    public static function postQuery(array $departments, array $results)
    {
        foreach ($departments as $department) {
            foreach ($results as $result) {
                if ($result->id === $department->id) {
                    
                    $department->fromResults($result);
                    break;
                }
            }
        }
        
        // Query for all expanded relationships
    }

    /**
     *
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        assert('\Loris\Utility::isString($results, \'address\') /* property must be a string */');
        $this->address = $results->address;

        assert('\Loris\Utility::isString($results, \'building\') /* property must be a string */');
        $this->building = $results->building;

        assert('\Loris\Utility::isString($results, \'id\') /* property must be a string */');
        $this->id = $results->id;

        assert('\Loris\Utility::isString($results, \'shortTitle\') /* property must be a string */');
        $this->shortTitle = $results->shortTitle;

        assert('\Loris\Utility::isString($results, \'title\') /* property must be a string */');
        $this->title = $results->title;

        // Perform expansions after hydration, in case we hydrated any
        // additional resource references in Arrays or Objects
        $this->doExpansions();
    }

    /**
     * 
     * @param array $resources
     */
    public function expand(array $resources)
    {

        $this->expansions = $resources;
    }

    /**
     * Perform actual expansions after hydration.
     *
     * This is added as a separate step in case we dynamically add 
     * additional resource references while hydrating from the data store
     * (e.g. resources stored in Arrays or Objects)
     */
    private function doExpansions()
    {
        if ($this->expansions === null) {
            return;
        }

    }

    /**
     * Serializes this resource and all expanded resource properties.
     * 
     * This method generates a simple object that can be passed to 
     * `json_encode` for final encoding. Complex type attributes (such
     * as DateTime) are automatically converted to a standard presentation.
     * 
     * @return \stdClass
     */
    public function serialize()
    {
        // Get serialized data from Meta
        $serialized = parent::serialize();

        $serialized->address = $this->address;

        $serialized->building = $this->building;

        $serialized->id = $this->id;

        $serialized->shortTitle = $this->shortTitle;

        $serialized->title = $this->title;

        return $serialized;
    }
}
