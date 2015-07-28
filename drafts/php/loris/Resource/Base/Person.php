<?php
namespace Loris\Resource\Base;

class Person extends Meta
{
    const URI = '/person/{id}';

    // Attributes
    public $osuid = null; // String
    public $firstName = null; // String
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

            $person->fromRowsets($results[$person->id()]);
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
     * @param array $rowsets
     */
    public function fromRowsets(array $rowsets)
    {
        /*
            Expect:
            array( // rowsets
                array( // rowset
                    array( // row
                        id, firstName, lastName, username, 
                        departmentId, coworkersId, coworkersTotal
                    )
                ),
                array( // rowset
                    array( // row
                        id, address1, address2, city, state, 
                        zip, room, building, phone
                    )
                )
            )
            TODO: This is too based on what SQL gives us. It would
            be better to write this in a way that is more OO, and the
            utility method that pulls it from SQL just does a better
            job at performing restructuring. 
        */
        $attribsRow = $rowsets[0][0];

        // Update id(), as we may have potentially not had it pre-query
        $this->id($attribsRow['id']);

        // Hydrate attributes
        $this->osuid = $attribsRow['osuid'];
        $this->firstName = $attribsRow['firstName'];
        $this->lastName = $attribsRow['lastName'];
        $this->username = $attribsRow['username'];
        $this->active = ($attribsRow['active'] == '1'); // TODO: Better casting
        $this->jobCode = intval($attribsRow['jobCode']);
        $this->jobDescription = $attribsRow['jobDescription'];
        $this->jobGroup = $attribsRow['jobGroup'];
        $this->apptCode = intval($attribsRow['apptCode']);
        $this->apptDescription = $attribsRow['apptDescription'];
        $this->fte = intval($attribsRow['fte']);

        // Hydrate relationships
        if ($attribsRow['departmentId'] !== null) {
            $this->department->id($attribsRow['departmentId']);
        } else {
            // Data source tells us there is no associated department
            $this->department = new \Loris\Resource\Base\NullResource();
        }
        
        if ($attribsRow['coworkersId'] !== null) {
            $this->coworkers->id($attribsRow['coworkersId']);
            $this->coworkers->meta->total = $attribsRow['coworkersTotal'];
        } else {
            // Data source tells us there are no associated coworkers
            $this->coworkers = new \Loris\Resource\Base\NullResource();
        }

        // Hydrate `addresses` from the second rowset
        foreach ($rowsets[1] as $row) {
            $address = new \stdClass();
            $address->address1 = $row['address1'];
            $address->address2 = $row['address2'];
            $address->city = $row['city'];
            $address->state = $row['state'];
            $address->zip = $row['zip'];
            $address->room = $row['room'];
            $address->building = $row['building'];
            $address->phone = $row['phone'];
            array_push($this->addresses, $address);
        }
    }

    /**
     * @todo generator pattern
     *
     * @param array $resources
     */
    public function expand(array $resources)
    {
        // Sub-collection relationship
        if (array_key_exists('coworkers', $resources)) {

            //$this->coworkers = Discovery::find(
            //    $this->coworkers->meta->uri
            //);

            // For now, assume local.
            $this->coworkers = new \Loris\Resource\PersonCoworkers(
                $this->coworkers->id()
            );

            if (is_array($resources['coworkers'])) {
                $this->coworkers->expand($resources['coworkers']);
            }
        }

        // Other resource relationship
        if (array_key_exists('department', $resources)) {
            $this->department = new \Loris\Resource\Department(
                $this->department->id()
            );

            if (is_array($resources['department'])) {
                $this->department->expand($resources['department']);
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

