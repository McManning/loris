<?php
namespace Loris\Resource\Base;

use \Loris\Utility;

class TopLevelResource extends Meta
{
    const URI = '/resource';

    private $expansions = null;

    /**
     * This resource does not have any IDs
     */
    private $_id = null;

    // Properties

    /** type: resource */
    public $recursiveTopLevelResourceProp = null;
    /** type: string */
    public $stringProp = null;
    /** type: collection */
    public $topLevelCollectionProp = null;

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

        $this->recursiveTopLevelResourceProp = new Meta(
            array(),
            '/resource'
        );

        $this->topLevelCollectionProp = new MetaCollection(
            array(),
            '/collection'
        );

    }

    /**
     *
     * @param array(TopLevelResource) $topLevelResources
     */
    public static function query(array $topLevelResources)
    {
        throw new \Exception(
            'Base\\TopLevelResource::query() cannot be called directly.'
        );
    }

    /**
     *
     * @param array(TopLevelResource) $topLevelResources
     * @param \stdClass $results
     */
    public static function postQuery(array $topLevelResources, \stdClass $results)
    {
        foreach ($topLevelResources as $topLevelResource) {
 
            $topLevelResource->fromResults($results);
        }

        // Query for all expanded relationships
        $recursiveTopLevelResourceProps = array();
        $recursiveTopLevelResourcePropModel = \Loris\Discovery::find(
            $topLevelResources[0]->recursiveTopLevelResourceProp->uri()
        );
        foreach ($topLevelResources as $topLevelResource) {
            if ($topLevelResource->recursiveTopLevelResourceProp instanceof $recursiveTopLevelResourcePropModel->class) {
                $recursiveTopLevelResourceProps[] = $topLevelResource->recursiveTopLevelResourceProp;
            }
        }
        if (!empty($recursiveTopLevelResourceProps)) {
            call_user_func(
                array($recursiveTopLevelResourcePropModel->class, 'query'),
                $recursiveTopLevelResourceProps
            );
        }

        $topLevelCollectionProps = array();
        $topLevelCollectionPropModel = \Loris\Discovery::find(
            $topLevelResources[0]->topLevelCollectionProp->uri()
        );
        foreach ($topLevelResources as $topLevelResource) {
            if ($topLevelResource->topLevelCollectionProp instanceof $topLevelCollectionPropModel->class) {
                $topLevelCollectionProps[] = $topLevelResource->topLevelCollectionProp;
            }
        }
        if (!empty($topLevelCollectionProps)) {
            call_user_func(
                array($topLevelCollectionPropModel->class, 'query'),
                $topLevelCollectionProps
            );
        }

    }

    /**
     *
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        if ($results->recursiveTopLevelResourceProp != null) {
        
            $this->recursiveTopLevelResourceProp->updateMetaUri();
        } else {
            $this->recursiveTopLevelResourceProp = new NullResource(
                $this->recursiveTopLevelResourceProp->uri()
            );
        }

        assert('\Loris\Utility::isString($results, \'stringProp\') /* property must be a string */');
        $this->stringProp = $results->stringProp;

        assert('\Loris\Utility::isNumber($results, \'topLevelCollectionPropTotal\') /* collection total must be a number */');
        if ($results->topLevelCollectionProp != null) {
        
            $this->topLevelCollectionProp->meta->total = intval(
                $results->topLevelCollectionPropTotal
            );
            $this->topLevelCollectionProp->updateMetaUri();
        } else {
            $this->topLevelCollectionProp = new NullResource(
                $this->topLevelCollectionProp->uri()
            );
        }

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

        if (array_key_exists('recursiveTopLevelResourceProp', $this->expansions)) {

            $recursiveTopLevelResourcePropModel = \Loris\Discovery::find(
                $this->recursiveTopLevelResourceProp->uri()
            );
            $this->recursiveTopLevelResourceProp = new $recursiveTopLevelResourcePropModel->class(
                $this->recursiveTopLevelResourceProp->ids()
            );

            if (is_array($this->expansions['recursiveTopLevelResourceProp'])) {
                $this->recursiveTopLevelResourceProp->expand($this->expansions['recursiveTopLevelResourceProp']);
            }
        }

        if (array_key_exists('topLevelCollectionProp', $this->expansions)) {

            $topLevelCollectionPropModel = \Loris\Discovery::find(
                $this->topLevelCollectionProp->uri()
            );
            $this->topLevelCollectionProp = new $topLevelCollectionPropModel->class(
                $this->topLevelCollectionProp->ids(),
                $this->topLevelCollectionProp->meta->page,
                $this->topLevelCollectionProp->meta->limit
            );

            if (is_array($this->expansions['topLevelCollectionProp'])) {
                $this->topLevelCollectionProp->expand($this->expansions['topLevelCollectionProp']);
            }
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

        $serialized->recursiveTopLevelResourceProp = $this->recursiveTopLevelResourceProp->serialize();

        $serialized->stringProp = $this->stringProp;

        $serialized->topLevelCollectionProp = $this->topLevelCollectionProp->serialize();

        return $serialized;
    }
}
