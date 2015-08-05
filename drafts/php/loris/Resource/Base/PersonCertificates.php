<?php
namespace Loris\Resource\Base;

/*
    YAML Schema:

  PersonCertificates:
    properties:
      meta:
        x-meta-uri: "/person/{id}/certificates"
        $ref: "#/definitions/Meta"
      COI:
        type: object
        description: From USP_COI_getCOIStatus
        properties:
          title:
            type: string
          year:
            type: string
          expiration:
            type: string
            format: date
          status:
            type: string
          phs: 
            type: string
          companies:
            type: array
            items:
              type: string
      PIE:
        type: object
        description: From USP_isPIEligible
        properties:
          title:
            type: string
          eligible:
            type: boolean
      ibcGeneral:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          status:
            type: string
      ibcHGT:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          status:
            type: string
      ibcPlants:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          status:
            type: string
      animalCare:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          expired:
            type: string
            format: date
          status:
            type: string
      occHealth:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          expired:
            type: string
            format: date
          status:
            type: string
      OHR:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          expired:
            type: string
            format: date
          status:
            type: string
          workflow:
            type: string
      CITI:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          expired:
            type: string
            format: date
      GCP:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
          expired:
            type: string
            format: date
      RCR:
        type: object
        properties:
          title:
            type: string
          received:
            type: string
            format: date
      eProtocolUA:
        type: object
        properties:
          title:
            type: string
          narrative:
            type: string
*/
class PersonCertificates extends Meta
{
    const URI = '/person/{id}/certificates';

    protected $expansions = null;

    // Attributes
    public $COI = null; // stdClass
    public $PIE = null; // stdClass
    public $ibcGeneral = null; // stdClass
    public $ibcHGT = null; // stdClass
    public $ibcPlants = null; // stdClass
    public $animalCare = null; // stdClass
    public $occHealth = null; // stdClass
    public $OHR = null; // stdClass
    public $CITI = null; // stdClass
    public $GCP = null; // stdClass
    public $RCR = null; // stdClass
    public $eProtocolUA = null; // stdClass

    /**
     * @param string $id
     */
    function __construct($id)
    {
        parent::__construct($id, self::URI);
    }

    /**
     * @param array(PersonCertificates) $personCertificatess
     */
    public static function query(array $personCertificatess)
    {
        throw new \Exception(
            'Base\\PersonCertificates::query() cannot be called directly.'
        );
    }

    public static function postQuery(array $personCertificatess, array $results)
    {
        foreach ($personCertificatess as $personCertificates) {
            if (!array_key_exists($personCertificates->id(), $results)) {
                throw new \Exception(
                    'Person [' . $personCertificates->id() . '] missing from $results'
                );
            }

            $personCertificates->fromResults($results[$personCertificates->id()]);
        }
    }

    /**
     * @param array $results
     */
    public function fromResults(array $results)
    {
        /*
            Expect:
            @todo in a dev environment, need a way to validate input
            schema against the expected, and throw DETAILED errors on 
            exactly where the results differ.
            
            array(
                id,
                COI = array(
                    title, year, expiration, status, phs,
                    companies = array(string)
                ),
                PIE = array(title, eligible),
                ibcGeneral = array(title, received, status),
                ibcHGT = array(title, received, status),
                ibcPlants = array(title, received, status),
                animjalCare = array(title, received, expired, status),
                occHealth = array(title, received, expired, status),
                OHR = array(title, received, expired, status, workflow),
                CITI = array(title, received, expired),
                GCP = array(title, received, expired),
                RCR = array(title, received),
                eProtocolUA = array(title, narrative)
            )
        */
        // Update id(), as we may have potentially not had it pre-query
        $this->id($results['id']);

        // Hydrate attributes
        $this->COI->title = $results['COI']['title'];
        $this->COI->year = $results['COI']['year'];
        $this->COI->expiration = $results['COI']['expiration'];
        $this->COI->status = $results['COI']['status'];
        $this->COI->phs = $results['COI']['phs'];
        $this->COI->companies = $results['COI']['companies'];


        // Hydrate relationships
        if ($results['departmentId'] !== null) {
            $this->department->id($results['departmentId']);
        } else {
            // Data source tells us there is no associated department
            $this->department = new NullResource();
        }
        
        if ($results['coworkersId'] !== null) {
            $this->coworkers->id($results['coworkersId']);
            $this->coworkers->meta->total = intval($results['coworkersTotal']);
        } else {
            // Data source tells us there are no associated coworkers
            $this->coworkers = new NullResource();
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

        /// Hydrate `otherDepartments` array of objects, with a sub-resource
        foreach ($results['otherDepartments'] as $row) {
            $object = new \stdClass();
            $object->fte = intval($row['fte']);

            // Note we have to actually construct the resource here, 
            // as it wouldn't already exist (obviously)
            if ($row['departmentId'] !== null) {
                $object->department = new Meta(
                    $row['departmentId'], 
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

        // Array of stdClass that have a resource attribute
        if (array_key_exists('otherDepartments', $this->expansions)) {
            foreach ($this->otherDepartments as $otherDepartment) {

                // Test the resource attribute for expansions
                if (array_key_exists('department', $this->expansions['otherDepartments'])) {   
                    $otherDepartment->department = new \Loris\Resource\Department(
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
