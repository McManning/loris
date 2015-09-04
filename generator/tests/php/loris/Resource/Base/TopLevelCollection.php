<?php
namespace Loris\Resource\Base;

class TopLevelCollection extends MetaCollection
{
    const URI = '/collection';

    public $collection = null; // Array(SimpleResource)
    protected $expansions = null; // Array

    /**
     * This resource does not have any IDs
     */
    private $_id = null;

    // Properties


    /**
     * @param array $ids Unique identifiers for this collection
     */
    function __construct($ids)
    {
        parent::__construct($ids, self::URI);
    }

    /**
     *
     * @param array(TopLevelCollection) $topLevelCollections
     */
    public static function query(array $topLevelCollections)
    {
        throw new \Exception(
            'Base\\TopLevelCollection::query() cannot be called directly.'
        );
    }

    /**
     *
     * @param array(TopLevelCollection) $topLevelCollections
     * @param array $results
     */
    public static function postQuery(array $topLevelCollections, array $results)
    {
        foreach ($topLevelCollections as $topLevelCollection) {
            $found = false;
            foreach ($results as $result) {
                if (                    $result->page == $topLevelCollection->meta->page &&
                    $result->limit == $topLevelCollection->meta->limit) {
                    
                    $topLevelCollection->fromResults($result);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $ids = array(
                );
                throw new \Exception(
                    'TopLevelCollection <' . implode(', ', $ids) . 
                    ', page=' . $topLevelCollection->meta->page . 
                    ', limit= ' . $topLevelCollection->meta->limit . 
                    '> missing from query'
                );
            }
        }

        // For all collections, join their hydrated resources for a query
        $simpleResources = array();
        foreach ($topLevelCollections as $topLevelCollection) {
            if (count($topLevelCollection->collection) > 0) {
                $simpleResources = array_merge(
                    $simpleResources, 
                    $topLevelCollection->collection
                );
            }
        }

        if (count($simpleResources) > 0) {
            
            // Resolve to a SimpleResource or ExternalResource
            $simpleResourceModel = \Loris\Discovery::find(
                '/object-resource/{id}'
            );

            // Execute query for set of resources
            call_user_func(
                array($simpleResourceModel->class, 'query'), 
                $simpleResources
            );
        }
    }

    /**
     * @param array $results
     */
    public function fromResults(\stdClass $results)
    {

        assert('\Loris\Utility::isNumber($results, \'page\') /* property must be a number */');
        $this->meta->page = intval($results->page);

        assert('\Loris\Utility::isNumber($results, \'limit\') /* property must be a number */');
        $this->meta->limit = intval($results->limit);

        assert('\Loris\Utility::isNumber($results, \'total\') /* property must be a number */');
        $this->meta->total = intval($results->total);

        $this->collection = array();

        // Resolve resource URI into a model
        $simpleResourceModel = \Loris\Discovery::find(
            '/object-resource/{id}'
        );

        // Add a SimpleResource for each ID
        foreach ($results->collection as $row) {
            // Note we resolve the model here instead of doExpansions
            // as no matter what, if a collection is hydrated, the
            // collection items must also be hydrated. 
            $simpleResource = new $simpleResourceModel->class(
                array('id' => $row->simpleResourceId)
            );

            $this->collection[] = $simpleResource;
        }

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
     * Perform actual expansions after hydration, in case we dynamically
     * add additional resource references while hydrating from the data store
     * (e.g. resources stored in Arrays or Objects)
     */
    private function doExpansions()
    {
        if ($this->expansions === null) {
            return;
        }

        foreach ($this->collection as $simpleResource) {
            $simpleResource->expand($this->expansions);
        }
    }

    /**
     * @return stdClass
     */
    public function serialize()
    {
        // Get serialized data from MetaCollection
        $serialized = parent::serialize();

        // Add a collection if we've been hydrated
        if ($this->collection) {
            $serialized->collection = array();

            // Serialize all of our SimpleResources
            foreach ($this->collection as $simpleResource) {
                $serialized->collection[] = $simpleResource->serialize();
            }
        }
        
        return $serialized;
    }
}
