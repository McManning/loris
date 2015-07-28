<?php
namespace Loris\Resource\Base;

class Department extends Meta
{
    const URI = '/department/{id}';

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

            $department->fromRowsets($results[$department->id()]);
        }
    }

    /**
     * @param array $rowsets
     */
    public function fromRowsets(array $rowsets)
    {
        /*
            Expect:
            array( // rowsets
                array( // rowset
                    array( // row
                        id, title, shortTitle, building, address
                    )
                )
            )
        */
        $attribsRow = $rowsets[0][0];

        // Update id(), as we may have potentially not had it pre-query
        $this->id($attribsRow['id']);

        // Hydrate attributes
        $this->title = $attribsRow['title'];
        $this->shortTitle = $attribsRow['shortTitle'];
        $this->building = $attribsRow['building'];
        $this->address = $attribsRow['address'];
    }

    /**
     * @param array $resources
     */
    public function expand(array $resources)
    {
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

