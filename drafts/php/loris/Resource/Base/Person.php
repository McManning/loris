<?php
namespace Loris\Resource\Base;

class Person extends Meta
{
    const URI = '/person/{id}';

    private $expansions = null;

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
        $personCoworkers = array();
        $departments = array();

        // TODO: How would I resolve this? I shouldn't rely on the first Person
        // resource in the list, as they may not even have an object here. But
        // at the same time, I don't want to do discovery/resolution for every
        // single instance (since we KNOW they're all the same).
        // One method would be to walk until we find the reference not an 
        // instanceof NullResource.

        //$personCoworkersModel = \Loris\Discovery::find($)

        foreach ($persons as $person) {
            if ($person->coworkers instanceof \Loris\Resource\PersonCoworkers) {
                array_push($personCoworkers, $person->coworkers);
            }

            if ($person->department instanceof \Loris\Resource\Department) {
                array_push($departments, $person->department);
            }
        }

        if (!empty($personCoworkers)) {
            \Loris\Resource\PersonCoworkers::query($personCoworkers);
        }

        if (!empty($departments)) {
            \Loris\Resource\Department::query($departments);
        }
    }

    /**
     * @todo generator pattern (?)
     *
     * @param array $results
     */
    public function fromResults(array $results)
    {
        /*
            Expect:
            array( // rowsets
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
                addresses = array(
                    address1,
                    address2,
                    city,
                    state,
                    zip,
                    room,
                    building,
                    phone
                )
            )
        */
        // Update id(), as we may have potentially not had it pre-query
        $this->id($results['id']);

        // Hydrate attributes
        $this->osuid = $results['osuid'];
        $this->firstName = $results['firstName'];
        $this->middleName = $results['middleName'];
        $this->lastName = $results['lastName'];
        $this->username = $results['username'];
        $this->active = ($results['active'] == '1'); // TODO: Better casting
        $this->jobCode = intval($results['jobCode']);
        $this->jobDescription = $results['jobDescription'];
        $this->jobGroup = $results['jobGroup'];
        $this->apptCode = intval($results['apptCode']);
        $this->apptDescription = $results['apptDescription'];
        $this->fte = intval($results['fte']);

        // Hydrate relationships
        if ($results['departmentId'] !== null) {
            $this->department->id($results['departmentId']);
        } else {
            // Data source tells us there is no associated department
            $this->department = new \Loris\Resource\Base\NullResource();
        }
        
        if ($results['coworkersId'] !== null) {
            $this->coworkers->id($results['coworkersId']);
            $this->coworkers->meta->total = intval($results['coworkersTotal']);
        } else {
            // Data source tells us there are no associated coworkers
            $this->coworkers = new \Loris\Resource\Base\NullResource();
        }

        // Hydrate `addresses` array of objects
        foreach ($results['addresses'] as $row) {
            $object = new \stdClass();
            $object->address1 = $row['address1'];
            $object->address2 = $row['address2'];
            $object->city = $row['city'];
            $object->state = $row['state'];
            $object->zip = $row['zip'];
            $object->room = $row['room'];
            $object->building = $row['building'];
            $object->phone = $row['phone'];
            array_push($this->addresses, $object);
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

            //$this->coworkers = Discovery::find(
            //    $this->coworkers->meta->uri
            //);

            // For now, assume local.
            $this->coworkers = new \Loris\Resource\PersonCoworkers(
                $this->coworkers->id()
            );

            if (is_array($this->expansions['coworkers'])) {
                $this->coworkers->expand($this->expansions['coworkers']);
            }
        }

        // Other resource relationship
        if (array_key_exists('department', $this->expansions)) {
            $this->department = new \Loris\Resource\Department(
                $this->department->id()
            );

            if (is_array($this->expansions['department'])) {
                $this->department->expand($this->expansions['department']);
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

        return $serialized;
    }
}

