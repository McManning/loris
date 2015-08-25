<?php
namespace Loris\Resource\Base;

class CollegeDepartments extends MetaCollection
{
    const URI = '/college/{id}/departments';

    public $collection = null; // Array(Department)
    protected $expansions = null; // Array

    /**
     * Track the properties that are used as our collections 
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
    function __construct($ids = null, $page, $limit)
    {
        parent::__construct($ids, self::URI, $page, $limit);
    }

    /**
     *
     * @param array(CollegeDepartments) $collegeDepartmentss
     */
    public static function query(array $collegeDepartmentss)
    {
        throw new \Exception(
            'Base\\CollegeDepartments::query() cannot be called directly.'
        );
    }

    /**
     *
     * @param array(CollegeDepartments) $collegeDepartmentss
     * @param array $results
     */
    public static function postQuery(array $collegeDepartmentss, array $results)
    {
        foreach ($collegeDepartmentss as $collegeDepartments) {
            $found = false;
            foreach ($results as $result) {
                if ($result->id === $collegeDepartments->id) {
                    
                    $collegeDepartments->fromResults($result);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $ids = array(
                    'id=' . $collegeDepartments->id
                );
                throw new \Exception(
                    'CollegeDepartments <' . implode(', ', $ids) . '> missing from query'
                );
            }
        }

        // For all collections, join their hydrated resources for a query
        $departments = array();
        foreach ($collegeDepartmentss as $collegeDepartments) {
            if (count($collegeDepartments->collection) > 0) {
                $departments = array_merge(
                    $departments, 
                    $collegeDepartments->collection
                );
            }
        }

        if (count($departments) > 0) {
            
            // Resolve to a Department or ExternalResource
            $departmentModel = \Loris\Discovery::find(
                '/department/{id}'
            );

            // Execute query for set of resources
            call_user_func(
                array($departmentModel->class, 'query'), 
                $departments
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
        $departmentModel = \Loris\Discovery::find(
            '/department/{id}'
        );

        // Add a Department for each ID
        foreach ($results->collection as $row) {
            // Note we resolve the model here instead of doExpansions
            // as no matter what, if a collection is hydrated, the
            // collection items must also be hydrated. 
            $department = new $departmentModel->class(
                array('id' => $row->departmentId)
            );

            $this->collection[] = $department;
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

        foreach ($this->collection as $department) {
            $department->expand($this->expansions);
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

            // Serialize all of our Departments
            foreach ($this->collection as $department) {
                $serialized->collection[] = $department->serialize();
            }
        }
        
        return $serialized;
    }
}
