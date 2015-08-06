<?php
namespace Loris\Resource\Base;

class PersonCoworkers extends MetaCollection
{
    const URI = '/person/{id}/coworkers';

    public $collection = null; // Array(Person)
    protected $expansions = null; // Array

    /**
     * @param string $id
     */
    function __construct($id)
    {
        parent::__construct($id, self::URI);
    }

    /**
     * @param Array(PersonCoworkers) $collections
     */
    public static function query(array $collections)
    {
        throw new \Exception(
            'Base\\PersonCoworkers::query() cannot be called directly.'
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

        \Loris\Resource\Person::query($persons);
    }

    /**
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        // Hydrate meta attributes
        $this->id($results->id);
        $this->meta->page = intval($results->page);
        $this->meta->limit = intval($results->limit);
        $this->meta->total = intval($results->total);

        $this->collection = array();

        // Add a Person for each entry in our second rowset
        foreach ($results->ids as $id) {
            $person = new \Loris\Resource\Person(
                $id
            );

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
            foreach ($this->collection as $resource) {
                array_push(
                    $serialized->collection, 
                    $resource->serialize()
                );
            }
        }
        
        return $serialized;
    }
}

