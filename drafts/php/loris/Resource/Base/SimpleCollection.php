<?php
namespace Loris\Resource\Base;

class SimpleCollection extends MetaCollection
{
    const URI = '/object-collection/{id}';

    public $collection = null; // Array(SimpleResource)
    protected $expansions = null; // Array

    /**
     * Track the properties that are used as our resources 
     * distinct identifier (one or composite). Each match
     * within URI must exist within this list. 
     */
    private $_id = array(
        'id'
    );

    // Properties

    /** type: string */
    public $id = null;


    /**
     * @param array $ids Unique identifiers for this collection
     * @param integer $page Currently page of the collection to query (1-indexed)
     * @param integer $limit Number of results per collection page 
     */
    function __construct($ids, $page, $limit)
    {
        parent::__construct($ids, self::URI, $page, $limit);
    }

    /**
     *
     * @param array(SimpleCollection) $simpleCollections
     */
    public static function query(array $simpleCollections)
    {
        throw new \Exception(
            'Base\\SimpleCollection::query() cannot be called directly.'
        );
    }

    /**
     *
     * @param array(SimpleCollection) $simpleCollections
     * @param array $results
     */
    public static function postQuery(array $simpleCollections, array $results)
    {
        foreach ($simpleCollections as $simpleCollection) {
            $found = false;
            foreach ($results as $result) {
                if ($result->id == $simpleCollection->id &&
                    $result->page == $simpleCollection->meta->page &&
                    $result->limit == $simpleCollection->meta->limit) {
                    
                    $simpleCollection->fromResults($result);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $ids = array(
                    'id=' . $simpleCollection->id
                );
                throw new \Exception(
                    'SimpleCollection <' . implode(', ', $ids) . '> missing from query'
                );
            }
        }

        // For all collections, join their hydrated resources for a query
        $simpleResources = array();
        foreach ($simpleCollections as $simpleCollection) {
            if (count($simpleCollection->collection) > 0) {
                $simpleResources = array_merge(
                    $simpleResources, 
                    $simpleCollection->collection
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
        assert('\Loris\Utility::isString($results, \'id\') /* property must be a string */');
        $this->id = $results->id;

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
