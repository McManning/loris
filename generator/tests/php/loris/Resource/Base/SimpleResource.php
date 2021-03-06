<?php
namespace Loris\Resource\Base;

use \Loris\Utility;

class SimpleResource extends Meta
{
    const URI = '/object-resource/{id}';

    private $expansions = null;

    /**
     * Track the properties that are used as our resources 
     * distinct identifier (one or composite). Each match
     * within URI must exist within this list. 
     */
    private $_id = array(
        'id'     );

    // Properties

    /** type: string */
    public $id = null;

    /**
     * @param array $ids Unique identifiers for this resource
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
     * @param array(SimpleResource) $simpleResources
     */
    public static function query(array $simpleResources)
    {
        throw new \Exception(
            'Base\\SimpleResource::query() cannot be called directly.'
        );
    }

    /**
     *
     * @param array(SimpleResource) $simpleResources
     * @param array $results
     */
    public static function postQuery(array $simpleResources, array $results)
    {
        foreach ($simpleResources as $simpleResource) {
            $found = false;
            foreach ($results as $result) {
                if ($result->id === $simpleResource->id) {
                    
                    $simpleResource->fromResults($result);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $ids = array(
                    'id=' . $simpleResource->id
                );
                throw new \Exception(
                    'SimpleResource <' . implode(', ', $ids) . '> missing from query'
                );
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
        assert('\Loris\Utility::isString($results, \'id\') /* property must be a string */');
        $this->id = $results->id;

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

        $serialized->id = $this->id;

        return $serialized;
    }
}
