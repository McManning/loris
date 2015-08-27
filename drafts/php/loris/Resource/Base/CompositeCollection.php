<?php
namespace Loris\Resource\Base;

class CompositeCollection extends MetaCollection
{
    const URI = '/object-collection/{idLeft}/{idRight}';

    public $collection = null; // Array(CompositeResource)
    protected $expansions = null; // Array

    /**
     * Track the properties that are used as our resources 
     * distinct identifier (one or composite). Each match
     * within URI must exist within this list. 
     */
    private $_id = array(
        'idLeft', 'idRight'
    );

    // Properties

    /** type: string */
    public $idLeft = null;

    /** type: string */
    public $idRight = null;


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
     * @param array(CompositeCollection) $compositeCollections
     */
    public static function query(array $compositeCollections)
    {
        throw new \Exception(
            'Base\\CompositeCollection::query() cannot be called directly.'
        );
    }

    /**
     *
     * @param array(CompositeCollection) $compositeCollections
     * @param array $results
     */
    public static function postQuery(array $compositeCollections, array $results)
    {
        foreach ($compositeCollections as $compositeCollection) {
            $found = false;
            foreach ($results as $result) {
                if ($result->idLeft == $compositeCollection->idLeft &&
                    $result->idRight == $compositeCollection->idRight &&
                    $result->page == $compositeCollection->meta->page &&
                    $result->limit == $compositeCollection->meta->limit) {
                    
                    $compositeCollection->fromResults($result);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $ids = array(
                    'idLeft=' . $compositeCollection->idLeft,
                    'idRight=' . $compositeCollection->idRight
                );
                throw new \Exception(
                    'CompositeCollection <' . implode(', ', $ids) . '> missing from query'
                );
            }
        }

        // For all collections, join their hydrated resources for a query
        $compositeResources = array();
        foreach ($compositeCollections as $compositeCollection) {
            if (count($compositeCollection->collection) > 0) {
                $compositeResources = array_merge(
                    $compositeResources, 
                    $compositeCollection->collection
                );
            }
        }

        if (count($compositeResources) > 0) {
            
            // Resolve to a CompositeResource or ExternalResource
            $compositeResourceModel = \Loris\Discovery::find(
                '/object-resource/{idLeft}/{idRight}'
            );

            // Execute query for set of resources
            call_user_func(
                array($compositeResourceModel->class, 'query'), 
                $compositeResources
            );
        }
    }

    /**
     * @param array $results
     */
    public function fromResults(\stdClass $results)
    {
        assert('\Loris\Utility::isString($results, \'idLeft\') /* property must be a string */');
        $this->idLeft = $results->idLeft;
        assert('\Loris\Utility::isString($results, \'idRight\') /* property must be a string */');
        $this->idRight = $results->idRight;

        assert('\Loris\Utility::isNumber($results, \'page\') /* property must be a number */');
        $this->meta->page = intval($results->page);

        assert('\Loris\Utility::isNumber($results, \'limit\') /* property must be a number */');
        $this->meta->limit = intval($results->limit);

        assert('\Loris\Utility::isNumber($results, \'total\') /* property must be a number */');
        $this->meta->total = intval($results->total);

        $this->collection = array();

        // Resolve resource URI into a model
        $compositeResourceModel = \Loris\Discovery::find(
            '/object-resource/{idLeft}/{idRight}'
        );

        // Add a CompositeResource for each ID
        foreach ($results->collection as $row) {
            // Note we resolve the model here instead of doExpansions
            // as no matter what, if a collection is hydrated, the
            // collection items must also be hydrated. 
            $compositeResource = new $compositeResourceModel->class(
                array('idLeft' => $row->compositeResourceIdLeft, 'idRight' => $row->compositeResourceIdRight)
            );

            $this->collection[] = $compositeResource;
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

        foreach ($this->collection as $compositeResource) {
            $compositeResource->expand($this->expansions);
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

            // Serialize all of our CompositeResources
            foreach ($this->collection as $compositeResource) {
                $serialized->collection[] = $compositeResource->serialize();
            }
        }
        
        return $serialized;
    }
}
