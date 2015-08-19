<?php
namespace Loris\Resource\Base;

use \Loris\Utility;

class Person extends Meta
{
    const URI = '/person/{id}';

    private $expansions = null;

    /**
     * Track the properties that are used as our resource's 
     * distinct identifier (one or composite). Each match
     * within URI must exist within this list. 
     */
    private $_id = array(
        'id'
    );

    // Properties

    /** type: boolean */
    public $active = null;

    /** type: array */
    public $addresses = array();

    /**
     * Maintains a template of the inner object of 
     * $this->addresses for copying to new array items.
     */
    private $addressesTemplate = null;  

    /** type: string */
    public $apptCode = null;

    /** type: string */
    public $apptDescription = null;

    /** type: resource */
    public $department = null;

    /** type: string */
    public $firstName = null;

    /** type: number */
    public $fte = null;

    /** type: string */
    public $id = null;

    /** type: string */
    public $jobCode = null;

    /** type: string */
    public $jobDescription = null;

    /** type: string */
    public $jobGroup = null;

    /** type: string */
    public $lastName = null;

    /** type: string */
    public $middleName = null;

    /** type: string */
    public $osuid = null;

    /** type: array */
    public $otherDepartments = array();

    /**
     * Maintains a template of the inner object of 
     * $this->otherDepartments for copying to new array items.
     */
    private $otherDepartmentsTemplate = null;  

    /** type: string */
    public $username = null;

    /**
     * @param mixed $ids
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

        $this->addressesTemplate = new \stdClass;

        $this->department = new Meta(
            array('id' => null),
            '/department/{id}'
        );

        $this->otherDepartmentsTemplate = new \stdClass;

        $this->otherDepartmentsTemplate->department = new Meta(
            array('id' => null),
            '/department/{id}'
        );

    }

    /**
     *
     * @param array(Person) $persons
     */
    public static function query(array $persons)
    {
        throw new \Exception(
            'Base\\Person::query() cannot be called directly.'
        );
    }

    public static function postQuery(array $persons, array $results)
    {
        foreach ($persons as $person) {
            foreach ($results as $result) {
                if ($result->id === $person->id) {
                    
                    $person->fromResults($result);
                    break;
                }
            }
        }
        
        // Query for all expanded relationships
        $departments = array();
        $departmentModel = \Loris\Discovery::find(
            $persons[0]->department->uri()
        );
        foreach ($persons as $person) {
            if ($person->department instanceof $departmentModel->class) {
                $departments[] = $person->department;
            }
        }
        if (!empty($departments)) {
            call_user_func(
                array($departmentModel->class, 'query'),
                $departments
            );
        }

        $departments = array();
        $departmentModel = \Loris\Discovery::find(
            $persons[0]->otherDepartmentsTemplate->department->uri()
        );
        foreach ($persons as $person) {
            foreach ($person->otherDepartments as $item) {
                if ($item->department instanceof $departmentModel->class) {
                    $departments[] = $item->department;
                }
            }
        }
        if (!empty($departments)) {
            call_user_func(
                array($departmentModel->class, 'query'),
                $departments
            );
        }

    }

    /**
     *
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        assert('\Loris\Utility::isBool($results, \'active\') /* property must be a boolean */');
        $this->active = boolval(
            $results->active
        );

        $addressesCount = count($results->addresses);
        for ($i = 0; $i < $addressesCount; $i++) {
            $this->addresses[$i] = clone $this->addressesTemplate;

            assert('\Loris\Utility::isString($results->addresses[$i], \'address1\') /* property must be a string */');
            $this->addresses[$i]->address1 = $results->addresses[$i]->address1;

            assert('\Loris\Utility::isString($results->addresses[$i], \'address2\') /* property must be a string */');
            $this->addresses[$i]->address2 = $results->addresses[$i]->address2;

            assert('\Loris\Utility::isString($results->addresses[$i], \'building\') /* property must be a string */');
            $this->addresses[$i]->building = $results->addresses[$i]->building;

            assert('\Loris\Utility::isString($results->addresses[$i], \'city\') /* property must be a string */');
            $this->addresses[$i]->city = $results->addresses[$i]->city;

            assert('\Loris\Utility::isString($results->addresses[$i], \'phone\') /* property must be a string */');
            $this->addresses[$i]->phone = $results->addresses[$i]->phone;

            assert('\Loris\Utility::isString($results->addresses[$i], \'room\') /* property must be a string */');
            $this->addresses[$i]->room = $results->addresses[$i]->room;

            assert('\Loris\Utility::isString($results->addresses[$i], \'state\') /* property must be a string */');
            $this->addresses[$i]->state = $results->addresses[$i]->state;

            assert('\Loris\Utility::isString($results->addresses[$i], \'zip\') /* property must be a string */');
            $this->addresses[$i]->zip = $results->addresses[$i]->zip;

        }

        assert('\Loris\Utility::isString($results, \'apptCode\') /* property must be a string */');
        $this->apptCode = $results->apptCode;

        assert('\Loris\Utility::isString($results, \'apptDescription\') /* property must be a string */');
        $this->apptDescription = $results->apptDescription;

        assert('\Loris\Utility::isString($results, \'departmentId\') /* resource id must be a string */');
        if ($results->departmentId !== null) {
        
            $this->department->id = $results->departmentId;
            $this->department->updateMetaUri();
        } else {
            $this->department = new NullResource(
                $this->department->uri()
            );
        }

        assert('\Loris\Utility::isString($results, \'firstName\') /* property must be a string */');
        $this->firstName = $results->firstName;

        assert('\Loris\Utility::isNumber($results, \'fte\') /* property must be a number */');
        $this->fte = intval(
            $results->fte
        );

        assert('\Loris\Utility::isString($results, \'id\') /* property must be a string */');
        $this->id = $results->id;

        assert('\Loris\Utility::isString($results, \'jobCode\') /* property must be a string */');
        $this->jobCode = $results->jobCode;

        assert('\Loris\Utility::isString($results, \'jobDescription\') /* property must be a string */');
        $this->jobDescription = $results->jobDescription;

        assert('\Loris\Utility::isString($results, \'jobGroup\') /* property must be a string */');
        $this->jobGroup = $results->jobGroup;

        assert('\Loris\Utility::isString($results, \'lastName\') /* property must be a string */');
        $this->lastName = $results->lastName;

        assert('\Loris\Utility::isString($results, \'middleName\') /* property must be a string */');
        $this->middleName = $results->middleName;

        assert('\Loris\Utility::isString($results, \'osuid\') /* property must be a string */');
        $this->osuid = $results->osuid;

        $otherDepartmentsCount = count($results->otherDepartments);
        for ($i = 0; $i < $otherDepartmentsCount; $i++) {
            $this->otherDepartments[$i] = clone $this->otherDepartmentsTemplate;

            assert('\Loris\Utility::isString($results->otherDepartments[$i], \'departmentId\') /* resource id must be a string */');
            // if ($results->otherDepartments[$i]->departmentId !== null) {
            if ($results->otherDepartments[$i]->departmentId !== null) {
            
                $this->otherDepartments[$i]->department->id = $results->otherDepartments[$i]->departmentId;
                $this->otherDepartments[$i]->department->updateMetaUri();
            } else {
                $this->otherDepartments[$i]->department = new NullResource(
                    $this->otherDepartments[$i]->department->uri()
                );
            }

            assert('\Loris\Utility::isNumber($results->otherDepartments[$i], \'fte\') /* property must be a number */');
            $this->otherDepartments[$i]->fte = intval(
                $results->otherDepartments[$i]->fte
            );

        }

        assert('\Loris\Utility::isString($results, \'username\') /* property must be a string */');
        $this->username = $results->username;

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

        if (array_key_exists('department', $this->expansions)) {

            $departmentModel = \Loris\Discovery::find(
                $this->department->uri()
            );
            $this->department = new $departmentModel->class(
                $this->department->ids()
            );

            if (is_array($this->expansions['department'])) {
                $this->department->expand($this->expansions['department']);
            }
        }

        if (array_key_exists('otherDepartments', $this->expansions) &&
            array_key_exists('department', $this->expansions['otherDepartments']) &&
            count($this->otherDepartments) > 0) {

            $departmentModel = \Loris\Discovery::find(
                $this->otherDepartments[0]->department->uri()
            );
            $departmentExpanded = is_array(
                $this->expansions['otherDepartments']['department']
            );
            $otherDepartmentsCount = count($this->otherDepartments);
            for ($i = 0; $i < $otherDepartmentsCount; $i++) {
                $this->otherDepartments[$i]->department  = new $departmentModel->class(
                    $this->otherDepartments[$i]->department->ids()
                );

                if ($departmentExpanded) {
                    $this->otherDepartments[$i]->department->expand(
                        $this->expansions['otherDepartments']['department']
                    );
                }
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

        $serialized->active = $this->active;

        $serialized->addresses = array();
        $addressesCount = count($this->addresses);
        for ($i = 0; $i < $addressesCount; $i++) {
            $serialized->addresses[$i] = new \stdClass;
            $serialized->addresses[$i]->address1 = $this->addresses[$i]->address1;
            $serialized->addresses[$i]->address2 = $this->addresses[$i]->address2;
            $serialized->addresses[$i]->building = $this->addresses[$i]->building;
            $serialized->addresses[$i]->city = $this->addresses[$i]->city;
            $serialized->addresses[$i]->phone = $this->addresses[$i]->phone;
            $serialized->addresses[$i]->room = $this->addresses[$i]->room;
            $serialized->addresses[$i]->state = $this->addresses[$i]->state;
            $serialized->addresses[$i]->zip = $this->addresses[$i]->zip;
        }

        $serialized->apptCode = $this->apptCode;

        $serialized->apptDescription = $this->apptDescription;

        $serialized->department = $this->department->serialize();

        $serialized->firstName = $this->firstName;

        $serialized->fte = $this->fte;

        $serialized->id = $this->id;

        $serialized->jobCode = $this->jobCode;

        $serialized->jobDescription = $this->jobDescription;

        $serialized->jobGroup = $this->jobGroup;

        $serialized->lastName = $this->lastName;

        $serialized->middleName = $this->middleName;

        $serialized->osuid = $this->osuid;

        $serialized->otherDepartments = array();
        $otherDepartmentsCount = count($this->otherDepartments);
        for ($i = 0; $i < $otherDepartmentsCount; $i++) {
            $serialized->otherDepartments[$i] = new \stdClass;
            $serialized->otherDepartments[$i]->department = $this->otherDepartments[$i]->department->serialize();
            $serialized->otherDepartments[$i]->fte = $this->otherDepartments[$i]->fte;
        }

        $serialized->username = $this->username;

        return $serialized;
    }
}
