<?php
namespace Loris\Resource\Base;

class Person extends Meta
{
    const URI = '/person/{id}';

    protected $expansions = null;

    // Attributes
    public $osuid = null; // String
    public $firstName = null; // String
    public $middleName = null; // String
    public $lastName = null; // String
    public $username = null; // String
    public $active = null; // Boolean
    public $addresses = array(); // Array(stdClass)

    // Job attributes
    public $jobCode = null; // String (technicall #)
    public $jobDescription = null; // String
    public $jobGroup = null; // String
    public $apptCode = null; // String (techincally #)
    public $apptDescription = null; // String
    public $fte = null; // String (technically #)

    // Relationships
    public $coworkers = null; // PersonCoworkers
    public $department = null; // Department

    // Array of objects that have resources within them
    public $otherDepartments = array(); // Array(stdClass) 

    /**
     * @param string $id
     */
    function __construct($id)
    {
        parent::__construct($id, self::URI);

        // Create Meta(Collection)s for relationships
        // Note: The inclusion of a URI is required here as we can possibly
        // never replace Meta(Collection) with an actual class (if non-expanded)
        // but we still need to know the resource URI. The problem then becomes,
        // where do we get this URI? We can't guarantee to have access to Resource::URI
        // because resources may not exist on the same instance as this caller. 
        // This may be a job for Discovery to handle?
        $this->coworkers = new MetaCollection(
            null, // Technically same as our ID, but we can't confirm that until after the query
            '/person/{id}/coworkers'
        );

        $this->department = new Meta(
            null, // We don't know the ID until the query returns
            '/department/{id}'
        );
    }

    /**
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
            if (!array_key_exists($person->id(), $results)) {
                throw new \Exception(
                    'Person [' . $person->id() . '] missing from $results'
                );
            }

            $person->fromResults($results[$person->id()]);
        }

        // Query for all expanded relationships

        // Notice that we don't optimize loops together, we instead logically group
        // things based on what field we're affecting (as is easier to automate)
        // Since multiple loops theoretically have the same running time as one 
        // (although not TECHNICALLY true) I'll consider this speed equivalent. 

        // Ref collection
        $coworkerss = array();
        $coworkersModel = \Loris\Discovery::find('/person/{id}/coworkers');
        foreach ($persons as $person) {
            if ($person->coworkers instanceof $coworkersModel->class) {
                $coworkerss[] = $person->coworkers;
            }
        }
        if (!empty($coworkerss)) {
            call_user_func(
                array($coworkersModel->class, 'query'),
                $coworkerss
            );
        }

        // Ref resource
        $departments = array();
        $departmentModel = \Loris\Discovery::find('/department/{id}');
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

        // Array of objects with ref 
        $otherDepartmentsDepartments = array();
        $otherDepartmentsDepartmentModel = \Loris\Discovery::find('/department/{id}');
        foreach ($persons as $person) {
            foreach ($person->otherDepartments as $otherDepartments) {
                if ($otherDepartments->department instanceof $otherDepartmentsDepartmentModel->class) {
                    $otherDepartmentsDepartments[] = $otherDepartments->department;
                }
            }
        }
        if (!empty($otherDepartmentsDepartments)) {
            call_user_func(
                array($otherDepartmentsDepartmentModel->class, 'query'),
                $otherDepartmentsDepartments
            );
        }
    }

    /**
     * @todo generator pattern (?)
     *
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        /*
            Expect:
            stdClass(
                id,
                osuid,
                firstName,
                middleName,
                lastName,
                username,
                active,
                jobCode,
                jobDescription,
                jobGroup,
                apptCode,
                apptDescription,
                fte
                departmentId,
                coworkersId,
                coworkersTotal,
                addresses = array(stdClass(
                    address1,
                    address2,
                    city,
                    state,
                    zip,
                    room,
                    building,
                    phone
                )),
                otherDepartments = array(stdClass(
                    fte,
                    departmentId
                ))
            )
        */
        // Update id(), as we may have potentially not had it pre-query
        $this->id($results->id);

        // Hydrate attributes
        $this->osuid = $results->osuid;
        $this->firstName = $results->firstName;
        $this->middleName = $results->middleName;
        $this->lastName = $results->lastName;
        $this->username = $results->username;
        $this->active = ($results->active == '1'); // TODO: Better casting
        $this->jobCode = intval($results->jobCode);
        $this->jobDescription = $results->jobDescription;
        $this->jobGroup = $results->jobGroup;
        $this->apptCode = intval($results->apptCode);
        $this->apptDescription = $results->apptDescription;
        $this->fte = intval($results->fte);

        // Hydrate relationships
        if ($results->departmentId !== null) {
            $this->department->id($results->departmentId);
        } else {
            // Data source tells us there is no associated department
            $this->department = new NullResource();
        }
        
        if ($results->coworkersId !== null) {
            $this->coworkers->id($results->coworkersId);
            $this->coworkers->meta->total = intval($results->coworkersTotal);
        } else {
            // Data source tells us there are no associated coworkers
            $this->coworkers = new NullResource();
        }

        // Hydrate `addresses` array of objects
        foreach ($results->addresses as $row) {
            $object = new \stdClass();
            $object->address1 = $row->address1;
            $object->address2 = $row->address2;
            $object->city = $row->city;
            $object->state = $row->state;
            $object->zip = $row->zip;
            $object->room = $row->room;
            $object->building = $row->building;
            $object->phone = $row->phone;
            array_push($this->addresses, $object);
        }

        /// Hydrate `otherDepartments` array of objects, with a sub-resource
        foreach ($results->otherDepartments as $row) {
            $object = new \stdClass();
            $object->fte = intval($row->fte);

            // Note we have to actually construct the resource here, 
            // as it wouldn't already exist (obviously)
            if ($row->departmentId !== null) {
                $object->department = new Meta(
                    $row->departmentId, 
                    '/department/{id}'
                );
            } else {
                // Data source tells us there is no associated department
                $object->department = new NullResource();
            }

            array_push($this->otherDepartments, $object);
        }

        // Perform expansions after hydration, in case we hydrated any
        // additional resource references in Arrays or Objects
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

        // Sub-collection relationship
        if (array_key_exists('coworkers', $this->expansions)) {

            $personCoworkersModel = \Loris\Discovery::find('/person/{id}/coworkers');

            $this->coworkers = new $personCoworkersModel->class(
                $this->coworkers->id()
            );

            if (is_array($this->expansions['coworkers'])) {
                $this->coworkers->expand($this->expansions['coworkers']);
            }
        }

        // Other resource relationship
        if (array_key_exists('department', $this->expansions)) {

            $departmentModel = \Loris\Discovery::find('/department/{id}');

            $this->department = new $departmentModel->class(
                $this->department->id()
            );

            if (is_array($this->expansions['department'])) {
                $this->department->expand($this->expansions['department']);
            }
        }

        // Array of stdClass that have a resource attribute
        if (array_key_exists('otherDepartments', $this->expansions)) {
            foreach ($this->otherDepartments as $otherDepartment) {

                // Test the resource attribute for expansions
                if (array_key_exists('department', $this->expansions['otherDepartments'])) {  

                    $departmentModel = \Loris\Discovery::find('/department/{id}'); 

                    $otherDepartment->department = new $departmentModel->class(
                        $otherDepartment->department->id()
                    );

                    if (is_array($this->expansions['otherDepartments']['department'])) {
                        $otherDepartment->department->expand(
                            $this->expansions['otherDepartments']['department']
                        );
                    }
                }
            }
        }
    }

    /**
     * @todo generator pattern
     *
     * @return stdClass
     */
    public function serialize()
    {
        // Get serialized data from Meta
        $serialized = parent::serialize();

        // Attributes
        $serialized->osuid = $this->osuid;
        $serialized->firstName = $this->firstName;
        $serialized->middleName = $this->middleName;
        $serialized->lastName = $this->lastName;
        $serialized->active = $this->active;
        $serialized->addresses = $this->addresses;

        $serialized->jobCode = $this->jobCode;
        $serialized->jobDescription = $this->jobDescription;
        $serialized->jobGroup = $this->jobGroup;
        $serialized->apptCode = $this->apptCode;
        $serialized->apptDescription = $this->apptDescription;
        $serialized->fte = $this->fte;

        // Relationships
        $serialized->coworkers = $this->coworkers->serialize();
        $serialized->department = $this->department->serialize();

        // TODO: Problem is that we can't just copy for serialization. We have to
        // iterate properties and serialize the department resource manually.
        $serialized->otherDepartments = array(); 
        foreach ($this->otherDepartments as $otherDepartment) {
            $object = new \stdClass;
            $object->fte = $otherDepartment->fte;
            $object->department = $otherDepartment->department->serialize();
            array_push($serialized->otherDepartments, $object);
        }

        return $serialized;
    }
}

/*
  Source YML Schema:

  Person:
    properties:
      meta:
        x-meta-uri: "/person/{id}"
        $ref: "#/definitions/Meta"
      id:
        type: string
      osuid:
        type: string
      firstName:
        type: string
      middleName:
        type: string
      lastName:
        type: string
      username:
        type: string
      active:
        type: boolean
      jobCode:
        type: string
      jobDescription:
        type: string
      jobGroup:
        type: string
      apptCode:
        type: string
      apptDescription:
        type: string
      fte:
        type: number
      coworkers:
        x-meta-uri: "/person/{id}/coworkers"
        $ref: "#/resources/PersonCoworkers"
      department:
        x-meta-uri: "/department/{id}"
        $ref: "#/resources/Department"
      otherDepartments:
        type: array
        items:
          type: object
          properties:
            fte:
              type: number
            department:
              x-meta-uri: "/department/{id}"
              $ref: "#/resources/Department"
      addresses:
        type: array
        items:
          type: object
          properties:
            address1:
              type: string
            address2:
              type: string
            city:
              type: string
            state:
              type: string
            zip:
              type: string
            room:
              type: string
            building:
              type: string
            phone:
              type: string
*/
