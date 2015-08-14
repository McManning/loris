<?php
namespace Loris\Resource\Base;

use \Loris\Utility;

class GeneratedResource extends Meta
{
    const URI = '/test/{id}';

    private $expansions = null;

    // Properties

    /** 
     * An unordered list of references to collections, all of the same type
     * type: array
     */
    public $arrayOfCollectionProp = array();

    /**
     * Maintains a template of the inner collection of 
     * $this->arrayOfCollectionProp for copying to new array items.
     */
    private $arrayOfCollectionPropTemplate = null;  

    /** 
     * An unordered list of DateTime objects
     * type: array
     */
    public $arrayOfDateProp = array();

    /** 
     * An unordered list of numeric values
     * type: array
     */
    public $arrayOfNumberProp = array();

    /** 
     * An unordered list of complex objects, each with the same schema containing primitives or references
     * type: array
     */
    public $arrayOfObjectProp = array();

    /**
     * Maintains a template of the inner object of 
     * $this->arrayOfObjectProp for copying to new array items.
     */
    private $arrayOfObjectPropTemplate = null;  

    /** 
     * An unordered list of references to resources, all of the same type
     * type: array
     */
    public $arrayOfResourceProp = array();

    /**
     * Maintains a template of the inner resource of 
     * $this->arrayOfResourceProp for copying to new array items.
     */
    private $arrayOfResourcePropTemplate = null;  

    /** 
     * An unordered list of string values
     * type: array
     */
    public $arrayOfStringProp = array();

    /** 
     * A boolean value
     * type: boolean
     */
    public $boolProp = null;

    /** 
     * A reference to a collection
     * type: collection
     */
    public $collectionProp = null;

    /** 
     * A date value, stored as a PHP DateTime object
     * type: date
     */
    public $dateProp = null;

    /** 
     * A numeric value
     * type: number
     */
    public $numberProp = null;

    /** 
     * A complex object containing primitives or references
     * type: object
     */
    public $objectProp = null;

    /** 
     * A reference to another resource
     * type: resource
     */
    public $resourceProp = null;

    /** 
     * A generic string value
     * type: string
     */
    public $stringProp = null;

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
        $this->arrayOfCollectionPropTemplate = new MetaCollection(
            null,
            '/array-collection/{id}'
        );

        $this->arrayOfObjectPropTemplate = new \stdClass;

        $this->arrayOfObjectPropTemplate->aopCollectionProp = new MetaCollection(
            null,
            '/object-collection/{id}'
        );

        $this->arrayOfObjectPropTemplate->aopResourceProp = new Meta(
            null,
            '/object-resource/{id}'
        );

        $this->arrayOfResourcePropTemplate = new Meta(
            null,
            '/array-resource/{id}'
        );

        $this->collectionProp = new MetaCollection(
            null, 
            '/collection/{id}'
        );

        $this->objectProp = new \stdClass;

        $this->objectProp->opCollectionProp = new MetaCollection(
            null,
            '/object-collection/{id}'
        );

        $this->objectProp->opResourceProp = new Meta(
            null,
            '/object-resource/{id}'
        );

        $this->resourceProp = new Meta(
            null, 
            '/resource/{id}'
        );

    }

    /**
     *
     * @param array(GeneratedResource) $generatedResources
     */
    public static function query(array $generatedResources)
    {
        throw new \Exception(
            'Base\\GeneratedResource::query() cannot be called directly.'
        );
    }

    public static function postQuery(array $generatedResources, array $results)
    {
        foreach ($generatedResources as $generatedResource) {
            if (!array_key_exists($generatedResource->id(), $results)) {
                throw new \Exception(
                    'GeneratedResource [' . $generatedResource->id() . '] missing from $results'
                );
            }

            $generatedResource->fromResults($results[$generatedResource->id()]);
        }

        // Query for all expanded relationships
        $arrayOfCollectionProps = array();
        $arrayOfCollectionPropModel = \Loris\Discovery::find(
            $generatedResources[0]->arrayOfCollectionPropTemplate->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            foreach ($generatedResource->arrayOfCollectionProp as $item) {
                if ($item instanceof $arrayOfCollectionPropModel->class) {
                    $arrayOfCollectionProps[] = $item;
                }
            }
        }
        if (!empty($arrayOfCollectionProps)) {
            call_user_func(
                array($arrayOfCollectionPropModel->class, 'query'),
                $arrayOfCollectionProps
            );
        }

        $aopCollectionProps = array();
        $aopCollectionPropModel = \Loris\Discovery::find(
            $generatedResources[0]->arrayOfObjectPropTemplate->aopCollectionProp->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            foreach ($generatedResource->arrayOfObjectProp as $item) {
                if ($item->aopCollectionProp instanceof $aopCollectionPropModel->class) {
                    $aopCollectionProps[] = $item->aopCollectionProp;
                }
            }
        }
        if (!empty($aopCollectionProps)) {
            call_user_func(
                array($aopCollectionPropModel->class, 'query'),
                $aopCollectionProps
            );
        }

        $aopResourceProps = array();
        $aopResourcePropModel = \Loris\Discovery::find(
            $generatedResources[0]->arrayOfObjectPropTemplate->aopResourceProp->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            foreach ($generatedResource->arrayOfObjectProp as $item) {
                if ($item->aopResourceProp instanceof $aopResourcePropModel->class) {
                    $aopResourceProps[] = $item->aopResourceProp;
                }
            }
        }
        if (!empty($aopResourceProps)) {
            call_user_func(
                array($aopResourcePropModel->class, 'query'),
                $aopResourceProps
            );
        }

        $arrayOfResourceProps = array();
        $arrayOfResourcePropModel = \Loris\Discovery::find(
            $generatedResources[0]->arrayOfResourcePropTemplate->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            foreach ($generatedResource->arrayOfResourceProp as $item) {
                if ($item instanceof $arrayOfResourcePropModel->class) {
                    $arrayOfResourceProps[] = $item;
                }
            }
        }
        if (!empty($arrayOfResourceProps)) {
            call_user_func(
                array($arrayOfResourcePropModel->class, 'query'),
                $arrayOfResourceProps
            );
        }

        $collectionProps = array();
        $collectionPropModel = \Loris\Discovery::find(
            $generatedResources[0]->collectionProp->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            if ($generatedResource->collectionProp instanceof $collectionPropModel->class) {
                $collectionProps[] = $generatedResource->collectionProp;
            }
        }
        if (!empty($collectionProps)) {
            call_user_func(
                array($collectionPropModel->class, 'query'),
                $collectionProps
            );
        }

        $opCollectionProps = array();
        $opCollectionPropModel = \Loris\Discovery::find(
            $generatedResources[0]->objectProp->opCollectionProp->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            if ($generatedResource->objectProp->opCollectionProp instanceof $opCollectionPropModel->class) {
                $opCollectionProps[] = $generatedResource->objectProp->opCollectionProp;
            }
        }
        if (!empty($opCollectionProps)) {
            call_user_func(
                array($opCollectionPropModel->class, 'query'),
                $opCollectionProps
            );
        }

        $opResourceProps = array();
        $opResourcePropModel = \Loris\Discovery::find(
            $generatedResources[0]->objectProp->opResourceProp->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            if ($generatedResource->objectProp->opResourceProp instanceof $opResourcePropModel->class) {
                $opResourceProps[] = $generatedResource->objectProp->opResourceProp;
            }
        }
        if (!empty($opResourceProps)) {
            call_user_func(
                array($opResourcePropModel->class, 'query'),
                $opResourceProps
            );
        }

        $resourceProps = array();
        $resourcePropModel = \Loris\Discovery::find(
            $generatedResources[0]->resourceProp->_uri
        );
        foreach ($generatedResources as $generatedResource) {
            if ($generatedResource->resourceProp instanceof $resourcePropModel->class) {
                $resourceProps[] = $generatedResource->resourceProp;
            }
        }
        if (!empty($resourceProps)) {
            call_user_func(
                array($resourcePropModel->class, 'query'),
                $resourceProps
            );
        }

    }

    /**
     *
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        // Update id(), as we may have potentially not had it pre-query
        $this->id($results->id);

        foreach ($results->arrayOfCollectionProp as $item) {
            assert('isset($item->arrayCollectionId) /* arrayCollectionId must be supplied */');
            assert('isset($item->arrayCollectionTotal) /* arrayCollectionTotal must be supplied */');
            if ($item->arrayCollectionId !== null) {
                $arrayCollection = clone $this->arrayOfCollectionPropTemplate;
                $arrayCollection->id(
                    $item->arrayCollectionId
                );
                $arrayCollection->meta->total = intval(
                    $item->arrayCollectionTotal
                );
                $this->arrayOfCollectionProp[] = $arrayCollection;
            }
        }

        foreach ($results->arrayOfDateProp as $item) {
            assert('\Loris\Utility::isDate($item) /* property must be in date format */');
            $this->arrayOfDateProp[] = \DateTime::createFromFormat(
                'Y-m-d', 
                $item
            );
        }

        foreach ($results->arrayOfNumberProp as $item) {
            assert('\Loris\Utility::isNumber($item) /* property must be a number */');
            $this->arrayOfNumberProp[] = intval($item);
        }

        $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
        for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
            $this->arrayOfObjectProp[$i] = clone $this->arrayOfObjectPropTemplate;

            assert('\Loris\Utility::isBool($results->arrayOfObjectProp[$i]->aopBoolProp) /* property must be a boolean */');
            $this->arrayOfObjectProp[$i]->aopBoolProp = boolval(
                $results->arrayOfObjectProp[$i]->aopBoolProp
            );

            assert('$results->arrayOfObjectProp[$i]->aopCollectionPropId) /* collection id must be supplied */');
            assert('$results->arrayOfObjectProp[$i]->aopCollectionPropTotal) /* collection total must be supplied */');
            if ($results->arrayOfObjectProp[$i]->aopCollectionPropId !== null) {
                $this->arrayOfObjectProp[$i]->aopCollectionProp->id(
                    $results->arrayOfObjectProp[$i]->aopCollectionPropId
                );
                $this->arrayOfObjectProp[$i]->aopCollectionProp->meta->total = intval(
                    $results->arrayOfObjectProp[$i]->aopCollectionPropTotal
                );
            } else {
                $this->arrayOfObjectProp[$i]->aopCollectionProp = new NullResource();
            }

   
            assert('\Loris\Utility::isDate($results->arrayOfObjectProp[$i]->aopDateProp) /* property must be in date format */');
            $this->arrayOfObjectProp[$i]->aopDateProp = \DateTime::createFromFormat(
                'Y-m-d', 
                $results->arrayOfObjectProp[$i]->aopDateProp
            );

            assert('\Loris\Utility::isNumber($results->arrayOfObjectProp[$i]->aopNumberProp) /* property must be a number */');
            $this->arrayOfObjectProp[$i]->aopNumberProp = intval(
                $results->arrayOfObjectProp[$i]->aopNumberProp
            );

            assert('isset($results->arrayOfObjectProp[$i]->aopResourcePropId) /* resource id must be supplied */');
            if ($results->arrayOfObjectProp[$i]->aopResourcePropId !== null) {
                $this->arrayOfObjectProp[$i]->aopResourceProp->id(
                    $results->arrayOfObjectProp[$i]->aopResourcePropId
                );
            } else {
                $this->arrayOfObjectProp[$i]->aopResourceProp = new NullResource();
            }

            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i]->aopStringProp) /* property must be a string */');
            $this->arrayOfObjectProp[$i]->aopStringProp = $results->arrayOfObjectProp[$i]->aopStringProp;

        }

        foreach ($results->arrayOfResourceProp as $item) {
            assert('isset($item->arrayResourceId) /* arrayResourceId must be supplied */');
            if ($item->arrayResourceId !== null) {
                $arrayResource = clone $this->arrayOfResourcePropTemplate;
                $arrayResource->id(
                    $item->arrayResourceId
                );
                $this->arrayOfResourceProp[] = $arrayResource;
            }
        }

        foreach ($results->arrayOfStringProp as $item) {
            assert('\Loris\Utility::isString($item) /* property must be a string */');
            $this->arrayOfStringProp[] = $item;
        }

        assert('\Loris\Utility::isBool($results->boolProp) /* property must be a boolean */');
        $this->boolProp = boolval(
            $results->boolProp
        );

        assert('isset($results->collectionPropId) /* collection id must be supplied */');
        assert('isset($results->collectionPropTotal) /* collection total must be supplied */');
        if ($results->collectionPropId !== null) {
            $this->collectionProp->id(
                $results->collectionPropId
            );
            $this->collectionProp->meta->total = intval(
                $results->collectionPropTotal
            );
        } else {
            $this->collectionProp = new NullResource();
        }

        assert('\Loris\Utility::isDate($results->dateProp) /* property must be in date format */');
        $this->dateProp = \DateTime::createFromFormat(
            'Y-m-d', 
            $results->dateProp
        );

        assert('\Loris\Utility::isNumber($results->numberProp) /* property must be a number */');
        $this->numberProp = intval(
            $results->numberProp
        );

        assert('\Loris\Utility::isBool($results->objectProp->opBoolProp) /* property must be a boolean */');
        $this->objectProp->opBoolProp = boolval(
            $results->objectProp->opBoolProp
        );

        assert('isset($results->objectProp->opCollectionPropId) /* collection id must be supplied */');
        assert('isset($results->objectProp->opCollectionPropTotal) /* collection total must be supplied */');
        if ($results->objectProp->opCollectionPropId !== null) {
            $this->objectProp->opCollectionProp->id(
                $results->objectProp->opCollectionPropId
            );
            $this->objectProp->opCollectionProp->meta->total = intval(
                $results->objectProp->opCollectionPropTotal
            );
        } else {
            $this->objectProp->opCollectionProp = new NullResource();
        }

   
        assert('\Loris\Utility::isDate($results->objectProp->opDateProp) /* property must be in date format */');
        $this->objectProp->opDateProp = \DateTime::createFromFormat(
            'Y-m-d', 
            $results->objectProp->opDateProp
        );

        assert('\Loris\Utility::isNumber($results->objectProp->opNumberProp) /* property must be a number */');
        $this->objectProp->opNumberProp = intval(
            $results->objectProp->opNumberProp
        );

        assert('isset($results->objectProp->opResourcePropId) /* resource id must be supplied */');
        if ($results->objectProp->opResourcePropId !== null) {
            $this->objectProp->opResourceProp->id(
                $results->objectProp->opResourcePropId
            );
        } else {
            $this->objectProp->opResourceProp = new NullResource();
        }

        assert('\Loris\Utility::isString($results->objectProp->opStringProp) /* property must be a string */');
        $this->objectProp->opStringProp = $results->objectProp->opStringProp;

        assert('isset($results->resourcePropId) /* resource id must be supplied */');
        if ($results->resourcePropId !== null) {
            $this->resourceProp->id(
                $results->resourcePropId
            );
        } else {
            $this->resourceProp = new NullResource();
        }

        assert('\Loris\Utility::isString($results->stringProp) /* property must be a string */');
        $this->stringProp = $results->stringProp;

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

        if (array_key_exists('arrayOfCollectionProp', $this->expansions) &&
            count($this->arrayOfCollectionProp) > 0) {

            $arrayOfCollectionPropModel = \Loris\Discovery::find(
                $this->arrayOfCollectionProp[0]->_uri
            );
            $arrayOfCollectionPropExpanded = is_array(
                $this->expansions['arrayOfCollectionProp']
            );
            $arrayOfCollectionPropCount = count($this->arrayOfCollectionProp);
            for ($i = 0; $i < $arrayOfCollectionPropCount; $i++) {
                $this->arrayOfCollectionProp[$i] = new $arrayOfCollectionPropModel->class(
                    $this->arrayOfCollectionProp[$i]->id()
                );

                if ($arrayOfCollectionPropExpanded) {
                    $this->arrayOfCollectionProp[$i]->expand(
                        $this->expansions['arrayOfCollectionProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfObjectProp', $this->expansions) &&
            array_key_exists('aopCollectionProp', $this->expansions['arrayOfObjectProp']) &&
            count($this->arrayOfObjectProp) > 0) {

            $aopCollectionPropModel = \Loris\Discovery::find(
                $this->arrayOfObjectProp[0]->aopCollectionProp->_uri
            );
            $aopCollectionPropExpanded = is_array(
                $this->expansions['arrayOfObjectProp']['aopCollectionProp']
            );
            $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
            for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
                $this->arrayOfObjectProp[$i]->aopCollectionProp  = new $aopCollectionPropModel->class(
                    $this->arrayOfObjectProp[$i]->aopCollectionProp->id()
                );

                if ($aopCollectionPropExpanded) {
                    $this->arrayOfObjectProp[$i]->aopCollectionProp->expand(
                        $this->expansions['arrayOfObjectProp']['aopCollectionProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfObjectProp', $this->expansions) &&
            array_key_exists('aopResourceProp', $this->expansions['arrayOfObjectProp']) &&
            count($this->arrayOfObjectProp) > 0) {

            $aopResourcePropModel = \Loris\Discovery::find(
                $this->arrayOfObjectProp[0]->aopResourceProp->_uri
            );
            $aopResourcePropExpanded = is_array(
                $this->expansions['arrayOfObjectProp']['aopResourceProp']
            );
            $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
            for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
                $this->arrayOfObjectProp[$i]->aopResourceProp  = new $aopResourcePropModel->class(
                    $this->arrayOfObjectProp[$i]->aopResourceProp->id()
                );

                if ($aopResourcePropExpanded) {
                    $this->arrayOfObjectProp[$i]->aopResourceProp->expand(
                        $this->expansions['arrayOfObjectProp']['aopResourceProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfResourceProp', $this->expansions) &&
            count($this->arrayOfResourceProp) > 0) {

            $arrayOfResourcePropModel = \Loris\Discovery::find(
                $this->arrayOfResourceProp[0]->_uri
            );
            $arrayOfResourcePropExpanded = is_array(
                $this->expansions['arrayOfResourceProp']
            );
            $arrayOfResourcePropCount = count($this->arrayOfResourceProp);
            for ($i = 0; $i < $arrayOfResourcePropCount; $i++) {
                $this->arrayOfResourceProp[$i] = new $arrayOfResourcePropModel->class(
                    $this->arrayOfResourceProp[$i]->id()
                );

                if ($arrayOfResourcePropExpanded) {
                    $this->arrayOfResourceProp[$i]->expand(
                        $this->expansions['arrayOfResourceProp']
                    );
                }
            }
        }

        if (array_key_exists('collectionProp', $this->expansions)) {

            $collectionPropModel = \Loris\Discovery::find(
                $this->collectionProp->_uri
            );
            $this->collectionProp = new $collectionPropModel->class(
                $this->collectionProp->id()
            );

            if (is_array($this->expansions['collectionProp'])) {
                $this->collectionProp->expand($this->expansions['collectionProp']);
            }
        }

        if (array_key_exists('objectProp', $this->expansions) &&
            array_key_exists('opCollectionProp', $this->expansions['objectProp'])) {

            $opCollectionPropModel = \Loris\Discovery::find(
                $this->objectProp->opCollectionProp->_uri
            );
            $this->objectProp->opCollectionProp = new $opCollectionPropModel->class(
                $this->objectProp->opCollectionProp->id()
            );

            if (is_array($this->expansions['objectProp']['opCollectionProp'])) {
                $this->objectProp->opCollectionProp->expand(
                    $this->expansions['objectProp']['opCollectionProp']
                );
            }
        }

        if (array_key_exists('objectProp', $this->expansions) &&
            array_key_exists('opResourceProp', $this->expansions['objectProp'])) {

            $opResourcePropModel = \Loris\Discovery::find(
                $this->objectProp->opResourceProp->_uri
            );
            $this->objectProp->opResourceProp = new $opResourcePropModel->class(
                $this->objectProp->opResourceProp->id()
            );

            if (is_array($this->expansions['objectProp']['opResourceProp'])) {
                $this->objectProp->opResourceProp->expand(
                    $this->expansions['objectProp']['opResourceProp']
                );
            }
        }

        if (array_key_exists('resourceProp', $this->expansions)) {

            $resourcePropModel = \Loris\Discovery::find(
                $this->resourceProp->_uri
            );
            $this->resourceProp = new $resourcePropModel->class(
                $this->resourceProp->id()
            );

            if (is_array($this->expansions['resourceProp'])) {
                $this->resourceProp->expand($this->expansions['resourceProp']);
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

        $serialized->arrayOfCollectionProp = array();
        $arrayOfCollectionPropCount = count($this->arrayOfCollectionProp);
        for ($i = 0; $i < $arrayOfCollectionPropCount; $i++) {
            $serialized->arrayOfCollectionProp[] = $this->arrayOfCollectionProp[$i]->serialize();
        }

        $serialized->arrayOfDateProp = array();
        $arrayOfDatePropCount = count($this->arrayOfDateProp);
        for ($i = 0; $i < $arrayOfDatePropCount; $i++) {
            $serialized->arrayOfDateProp[$i] = $this->arrayOfDateProp[$i]->format('Y-m-d');
        }

        $serialized->arrayOfNumberProp = clone $this->arrayOfNumberProp;

        $serialized->arrayOfObjectProp = array();
        $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
        for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
            $serialized->arrayOfObjectProp[$i] = new \stdClass;
            $serialized->arrayOfObjectProp[$i]->aopBoolProp = $this->arrayOfObjectProp[$i]->aopBoolProp;
            $serialized->arrayOfObjectProp[$i]->aopCollectionProp = $this->arrayOfObjectProp[$i]->aopCollectionProp->serialize();
            $serialized->arrayOfObjectProp[$i]->aopDateProp = $this->arrayOfObjectProp[$i]->aopDateProp->format('Y-m-d');
            $serialized->arrayOfObjectProp[$i]->aopNumberProp = $this->arrayOfObjectProp[$i]->aopNumberProp;
            $serialized->arrayOfObjectProp[$i]->aopResourceProp = $this->arrayOfObjectProp[$i]->aopResourceProp->serialize();
            $serialized->arrayOfObjectProp[$i]->aopStringProp = $this->arrayOfObjectProp[$i]->aopStringProp;
        }

        $serialized->arrayOfResourceProp = array();
        $arrayOfResourcePropCount = count($this->arrayOfResourceProp);
        for ($i = 0; $i < $arrayOfResourcePropCount; $i++) {
            $serialized->arrayOfResourceProp[] = $this->arrayOfResourceProp[$i]->serialize();
        }

        $serialized->arrayOfStringProp = clone $this->arrayOfStringProp;

        $serialized->boolProp = $this->boolProp;

        $serialized->collectionProp = $this->collectionProp->serialize();

        $serialized->dateProp = $this->dateProp;

        $serialized->numberProp = $this->numberProp;

        $serialized->objectProp = new \stdClass;
        $serialized->objectProp->opBoolProp = $this->objectProp->opBoolProp;
        $serialized->objectProp->opCollectionProp = $this->objectProp->opCollectionProp->serialize();
        $serialized->objectProp->opDateProp = $this->objectProp->opDateProp->format('Y-m-d');
        $serialized->objectProp->opNumberProp = $this->objectProp->opNumberProp;
        $serialized->objectProp->opResourceProp = $this->objectProp->opResourceProp->serialize();
        $serialized->objectProp->opStringProp = $this->objectProp->opStringProp;

        $serialized->resourceProp = $this->resourceProp->serialize();

        $serialized->stringProp = $this->stringProp;

        return $serialized;
    }
}
