<?php
namespace Loris\Resource\Base;

class Department extends Meta
{
    const URI = '/department/{id}';

    protected $expansions = null;

    // Attributes
    public $title = null; // String
    public $dnode = null; // String
    public $dnodeTitle = null; // String

    /**
     * @param string $id
     */
    function __construct($id)
    {
        parent::__construct($id, self::URI);
    }

    /**
     * @param array(Person) $persons
     */
    public static function query(array $persons)
    {
        throw new \Exception('Base\\Department::query() cannot be called directly.');
    }

    public static function postQuery(array $departments, array $results)
    {
        foreach ($departments as $department) {
            if (!array_key_exists($department->id(), $results)) {
                throw new \Exception('Department [' . $department->id() . '] missing from $results');
            }

            $department->fromResults($results[$department->id()]);
        }
    }

    /**
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        /*
            Expect:
            stdClass( // attributes
                id = ...,
                title = ...,
                shortTitle = ...,
                building = ...,
                address = ....
            )
        */

        // Update id(), as we may have potentially not had it pre-query
        $this->id($results->id);

        // Hydrate attributes
        $this->title = $results->title;
        $this->shortTitle = $results->shortTitle;
        $this->building = $results->building;
        $this->address = $results->address;

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

        // noop
    }

    /**
     * @return stdClass
     */
    public function serialize()
    {
        // Get serialized data from Meta
        $serialized = parent::serialize();

        // Attributes
        $serialized->title = $this->title;
        $serialized->shortTitle = $this->shortTitle;
        $serialized->building = $this->building;
        $serialized->address = $this->address;

        return $serialized;
    }
}

