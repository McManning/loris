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
          year:
            type: number
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
        type: boolean
        description: From USP_isPIEligible
      ibcGeneral:
        type: object
        properties:
          received:
            type: string
            format: date
          status:
            type: string
      ibcHGT:
        type: object
        properties:
          received:
            type: string
            format: date
          status:
            type: string
      ibcPlants:
        type: object
        properties:
          received:
            type: string
            format: date
          status:
            type: string
      animalCare:
        type: object
        properties:
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
          received:
            type: string
            format: date
          expired:
            type: string
            format: date
      GCP:
        type: object
        properties:
          received:
            type: string
            format: date
          expired:
            type: string
            format: date
      RCR:
        type: object
        properties:
          received:
            type: string
            format: date
      eProtocolUA:
        type: string
        description: eProtocol narrative
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
                    'PersonCertificates [' . $personCertificates->id() . '] missing from $results'
                );
            }

            $personCertificates->fromResults($results[$personCertificates->id()]);
        }
    }

    /**
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        /*
            Expect:
            @todo in a dev environment, need a way to validate input
            schema against the expected, and throw DETAILED errors on 
            exactly where the results differ.

            stdClass(
                id,
                COI = stdClass(
                    year, expiration, status, phs,
                    companies = array(string)
                ),
                PIE = stdClass(eligible),
                ibcGeneral = stdClass(received, status),
                ibcHGT = stdClass(received, status),
                ibcPlants = stdClass(received, status),
                animalCare = stdClass(received, expired, status),
                occHealth = stdClass(received, expired, status),
                OHR = stdClass(received, expired, status, workflow),
                CITI = stdClass(received, expired),
                GCP = stdClass(received, expired),
                RCR = stdClass(received),
                eProtocolUA = stdClass(narrative)
            )
        */
        // Update id(), as we may have potentially not had it pre-query
        $this->id($results->id);

        // Hydrate attributes
        $this->COI = new \stdClass;
        $this->COI->year = ($results->COI->year !== null) ? intval($results->COI->year) : null;
        $this->COI->expiration = $results->COI->expiration;
        $this->COI->status = $results->COI->status;
        $this->COI->phs = $results->COI->phs;
        $this->COI->companies = array();
        foreach ($results->COI->companies as $row) {
            // For autogenerated code, this would perform type conversions
            // if, say, each row is of type number or date.
            $this->COI->companies[] = $row;
        }

        $this->PIE = ($results->PIE == 1);

        $this->ibcGeneral = new \stdClass;
        $this->ibcGeneral->received = $results->ibcGeneral->received;
        $this->ibcGeneral->status = $results->ibcGeneral->status;

        $this->ibcHGT = new \stdClass;
        $this->ibcHGT->received = $results->ibcHGT->received;
        $this->ibcHGT->status = $results->ibcHGT->status;

        $this->ibcPlants = new \stdClass;
        $this->ibcPlants->received = $results->ibcPlants->received;
        $this->ibcPlants->status = $results->ibcPlants->status;

        $this->animalCare = new \stdClass;
        $this->animalCare->received = $results->animalCare->received;
        $this->animalCare->expired = $results->animalCare->expired;
        $this->animalCare->status = $results->animalCare->status;
        
        $this->occHealth = new \stdClass;
        $this->occHealth->received = $results->occHealth->received;
        $this->occHealth->expired = $results->occHealth->expired;
        $this->occHealth->status = $results->occHealth->status;
        
        $this->OHR = new \stdClass;
        $this->OHR->received = $results->OHR->received;
        $this->OHR->expired = $results->OHR->expired;
        $this->OHR->status = $results->OHR->status;
        $this->OHR->workflow = $results->OHR->workflow;
        
        $this->CITI = new \stdClass;
        $this->CITI->received = $results->CITI->received;
        $this->CITI->expired = $results->CITI->expired;
        
        $this->GCP = new \stdClass;
        $this->GCP->received = $results->GCP->received;
        $this->GCP->expired = $results->GCP->expired;

        $this->RCR = new \stdClass;
        $this->RCR->received = $results->RCR->received;

        $this->eProtocolUA = $results->eProtocolUA;

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

        // NOOP
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
        $serialized->COI = new \stdClass;
        $serialized->COI->year = $this->COI->year;
        $serialized->COI->expiration = $this->COI->expiration;
        $serialized->COI->status = $this->COI->status;
        $serialized->COI->phs = $this->COI->phs;
        $serialized->COI->companies = array();
        foreach ($this->COI->companies as $row) {
            $serialized->COI->companies[] = $row;
        }

        $serialized->PIE = $this->PIE;

        $serialized->ibcGeneral = new \stdClass;
        $serialized->ibcGeneral->received = $this->ibcGeneral->received;
        $serialized->ibcGeneral->status = $this->ibcGeneral->status;

        $serialized->ibcHGT = new \stdClass;
        $serialized->ibcHGT->received = $this->ibcHGT->received;
        $serialized->ibcHGT->status = $this->ibcHGT->status;

        $serialized->ibcPlants = new \stdClass;
        $serialized->ibcPlants->received = $this->ibcPlants->received;
        $serialized->ibcPlants->status = $this->ibcPlants->status;

        $serialized->animalCare = new \stdClass;
        $serialized->animalCare->received = $this->animalCare->received;
        $serialized->animalCare->expired = $this->animalCare->expired;
        $serialized->animalCare->status = $this->animalCare->status;
        
        $serialized->occHealth = new \stdClass;
        $serialized->occHealth->received = $this->occHealth->received;
        $serialized->occHealth->expired = $this->occHealth->expired;
        $serialized->occHealth->status = $this->occHealth->status;
        
        $serialized->OHR = new \stdClass;
        $serialized->OHR->received = $this->OHR->received;
        $serialized->OHR->expired = $this->OHR->expired;
        $serialized->OHR->status = $this->OHR->status;
        $serialized->OHR->workflow = $this->OHR->workflow;
        
        $serialized->CITI = new \stdClass;
        $serialized->CITI->received = $this->CITI->received;
        $serialized->CITI->expired = $this->CITI->expired;
        
        $serialized->GCP = new \stdClass;
        $serialized->GCP->received = $this->GCP->received;
        $serialized->GCP->expired = $this->GCP->expired;

        $serialized->RCR = new \stdClass;
        $serialized->RCR->received = $this->RCR->received;

        $serialized->eProtocolUA = $this->eProtocolUA;

        return $serialized;
    }
}
