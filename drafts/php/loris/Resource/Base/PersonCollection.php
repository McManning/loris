<?php
namespace Loris\Resource\Base;

class PersonCollection extends MetaCollection
{
    const URI = '/person';

    public $collection = null; // Array(Person)
    protected $expansions = null; // Array

    /**
     * @param string $id
     */
    function __construct($id = null)
    {
        parent::__construct($id, self::URI);
    }

    /**
     * @param Array(PersonCollection) $collections
     */
    public static function query(array $collections)
    {
        throw new \Exception(
            'Base\\PersonCollection::query() cannot be called directly.'
        );
    }

    public static function postQuery(array $collections, array $results)
    {
        // Gather Persons that have been hydrated from all 
        // collections and query for all simultaneously.
        $persons = array();

        foreach ($collections as $collection) {
            $collection->fromResults($results[$collection->id()]);

            if (count($collection->collection) > 0) {
                $persons = array_merge(
                    $persons, 
                    $collection->collection
                );
            }
        }

        if (count($persons) > 0) {
            
            // Resolve resource model
            $model = \Loris\Discovery::find($persons[0]->_uri);

            // Execute query for set of resources
            call_user_func(
                array($model->class, 'query'), 
                $persons
            );
        }
    }

    /**
     * @param array $results
     */
    public function fromResults($results)
    {
        // Hydrate meta attributes
        $this->id($results['id']);
        $this->meta->page = intval($results['page']);
        $this->meta->limit = intval($results['limit']);
        $this->meta->total = intval($results['total']);

        $this->collection = array();

        // Resolve resource URI into a model
        $model = \Loris\Discovery::find('/person/{id}');

        // Add a Person for each entry in our second rowset
        foreach ($results['ids'] as $id) {
            // Note we resolve the model here instead of doExpansions
            // as no matter what, if a collection is hydrated, the
            // collection items must also be hydrated. 
            $person = new $model->class($id);

            array_push($this->collection, $person);
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

        foreach ($this->collection as $item) {
            $item->expand($this->expansions);
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

            // Serialize all of our collection items
            foreach ($this->collection as $resource)
            {
                array_push(
                    $serialized->collection, 
                    $resource->serialize()
                );
            }
        }
        
        return $serialized;
    }
}

/*
    PersonCollection:
      properties:
        meta:
          x-meta-uri: "/person"
          $ref: "#/definitions/MetaCollection"
        collection:
          type: array
          items:
            $ref: "#/definitions/Person"
*/
