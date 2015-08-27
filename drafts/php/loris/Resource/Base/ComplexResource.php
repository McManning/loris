<?php
namespace Loris\Resource\Base;

use \Loris\Utility;

class ComplexResource extends Meta
{
    const URI = '/test/{idLeft}/foo/{idRight}';

    private $expansions = null;

    /**
     * Track the properties that are used as our resources 
     * distinct identifier (one or composite). Each match
     * within URI must exist within this list. 
     */
    private $_id = array(
        'idLeft', 'idRight'     );

    // Properties

    /** 
     * An unordered list of references to collections, all
     * of the same type
     * 
     * type: array
     */
    public $arrayOfCollectionProp = array();

    /**
     * Maintains a template of the inner collection of 
     * $this->arrayOfCollectionProp for copying to new array items.
     */
    private $arrayOfCollectionPropTemplate = null;  
    /** 
     * An unordered list of references to collections,
     * all of the same type
     * 
     * type: array
     */
    public $arrayOfCompositeCollectionProp = array();

    /**
     * Maintains a template of the inner collection of 
     * $this->arrayOfCompositeCollectionProp for copying to new array items.
     */
    private $arrayOfCompositeCollectionPropTemplate = null;  
    /** 
     * An unordered list of references to resources, all 
     * of the same type
     * 
     * type: array
     */
    public $arrayOfCompositeResourceProp = array();

    /**
     * Maintains a template of the inner resource of 
     * $this->arrayOfCompositeResourceProp for copying to new array items.
     */
    private $arrayOfCompositeResourcePropTemplate = null;  
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
     * An unordered list of complex objects, each with the
     * same schema containing primitives or references
     * 
     * type: array
     */
    public $arrayOfObjectProp = array();

    /**
     * Maintains a template of the inner object of 
     * $this->arrayOfObjectProp for copying to new array items.
     */
    private $arrayOfObjectPropTemplate = null;  
    /** 
     * An unordered list of references to resources, all of 
     * the same type
     * 
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
     * A reference to a collection with a composite ID
     * 
     * type: collection
     */
    public $compositeCollectionProp = null;
    /** 
     * A reference to another resource with a composite ID
     * 
     * type: resource
     */
    public $compositeResourceProp = null;
    /** 
     * A date value, stored as a PHP DateTime object
     * 
     * type: date
     */
    public $dateProp = null;
    /** 
     * Composite identifier left
     * type: string
     */
    public $idLeft = null;
    /** 
     * Composite identifier right
     * type: string
     */
    public $idRight = null;
    /** 
     * A numeric value
     * type: number
     */
    public $numberProp = null;
    /** 
     * A complex object containing primitives or references
     * 
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
     * A reference to a top level resource that doesn't use 
     * an ID in the URI
     * 
     * type: resource
     */
    public $topLevelResourceProp = null;

    /**
     * @param array $ids Unique identifiers for this resource
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

        $this->arrayOfCollectionPropTemplate = new MetaCollection(
            array('id' => null),
            '/object-collection/{id}'
        );

        $this->arrayOfCompositeCollectionPropTemplate = new MetaCollection(
            array('idLeft' => null, 'idRight' => null),
            '/object-collection/{idLeft}/{idRight}'
        );

        $this->arrayOfCompositeResourcePropTemplate = new Meta(
            array('idLeft' => null, 'idRight' => null),
            '/object-resource/{idLeft}/{idRight}'
        );

        $this->arrayOfObjectPropTemplate = new \stdClass;

        $this->arrayOfObjectPropTemplate->aopCollectionProp = new MetaCollection(
            array('id' => null),
            '/object-collection/{id}'
        );

        $this->arrayOfObjectPropTemplate->aopCompositeCollectionProp = new MetaCollection(
            array('idLeft' => null, 'idRight' => null),
            '/object-collection/{idLeft}/{idRight}'
        );

        $this->arrayOfObjectPropTemplate->aopCompositeResourceProp = new Meta(
            array('idLeft' => null, 'idRight' => null),
            '/object-resource/{idLeft}/{idRight}'
        );

        $this->arrayOfObjectPropTemplate->aopResourceProp = new Meta(
            array('id' => null),
            '/object-resource/{id}'
        );

        $this->arrayOfResourcePropTemplate = new Meta(
            array('id' => null),
            '/object-resource/{id}'
        );

        $this->collectionProp = new MetaCollection(
            array('id' => null),
            '/object-collection/{id}'
        );

        $this->compositeCollectionProp = new MetaCollection(
            array('idLeft' => null, 'idRight' => null),
            '/object-collection/{idLeft}/{idRight}'
        );

        $this->compositeResourceProp = new Meta(
            array('idLeft' => null, 'idRight' => null),
            '/object-resource/{idLeft}/{idRight}'
        );

        $this->objectProp = new \stdClass;

        $this->objectProp->opCollectionProp = new MetaCollection(
            array('id' => null),
            '/object-collection/{id}'
        );

        $this->objectProp->opCompositeCollectionProp = new MetaCollection(
            array('idLeft' => null, 'idRight' => null),
            '/object-collection/{idLeft}/{idRight}'
        );

        $this->objectProp->opCompositeResourceProp = new Meta(
            array('idLeft' => null, 'idRight' => null),
            '/object-resource/{idLeft}/{idRight}'
        );

        $this->objectProp->opResourceProp = new Meta(
            array('id' => null),
            '/object-resource/{id}'
        );

        $this->resourceProp = new Meta(
            array('id' => null),
            '/object-resource/{id}'
        );

        $this->topLevelResourceProp = new Meta(
            array(),
            '/resource'
        );

    }

    /**
     *
     * @param array(ComplexResource) $complexResources
     */
    public static function query(array $complexResources)
    {
        throw new \Exception(
            'Base\\ComplexResource::query() cannot be called directly.'
        );
    }

    /**
     *
     * @param array(ComplexResource) $complexResources
     * @param array $results
     */
    public static function postQuery(array $complexResources, array $results)
    {
        foreach ($complexResources as $complexResource) {
            $found = false;
            foreach ($results as $result) {
                if ($result->idLeft === $complexResource->idLeft &&
                    $result->idRight === $complexResource->idRight) {
                    
                    $complexResource->fromResults($result);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $ids = array(
                    'idLeft=' . $complexResource->idLeft,
                    'idRight=' . $complexResource->idRight
                );
                throw new \Exception(
                    'ComplexResource <' . implode(', ', $ids) . '> missing from query'
                );
            }
        }

        // Query for all expanded relationships
        $arrayOfCollectionProps = array();
        $arrayOfCollectionPropModel = \Loris\Discovery::find(
            $complexResources[0]->arrayOfCollectionPropTemplate->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfCollectionProp as $item) {
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

        $arrayOfCompositeCollectionProps = array();
        $arrayOfCompositeCollectionPropModel = \Loris\Discovery::find(
            $complexResources[0]->arrayOfCompositeCollectionPropTemplate->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfCompositeCollectionProp as $item) {
                if ($item instanceof $arrayOfCompositeCollectionPropModel->class) {
                    $arrayOfCompositeCollectionProps[] = $item;
                }
            }
        }
        if (!empty($arrayOfCompositeCollectionProps)) {
            call_user_func(
                array($arrayOfCompositeCollectionPropModel->class, 'query'),
                $arrayOfCompositeCollectionProps
            );
        }

        $arrayOfCompositeResourceProps = array();
        $arrayOfCompositeResourcePropModel = \Loris\Discovery::find(
            $complexResources[0]->arrayOfCompositeResourcePropTemplate->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfCompositeResourceProp as $item) {
                if ($item instanceof $arrayOfCompositeResourcePropModel->class) {
                    $arrayOfCompositeResourceProps[] = $item;
                }
            }
        }
        if (!empty($arrayOfCompositeResourceProps)) {
            call_user_func(
                array($arrayOfCompositeResourcePropModel->class, 'query'),
                $arrayOfCompositeResourceProps
            );
        }

        $aopCollectionProps = array();
        $aopCollectionPropModel = \Loris\Discovery::find(
            $complexResources[0]->arrayOfObjectPropTemplate->aopCollectionProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfObjectProp as $item) {
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

        $aopCompositeCollectionProps = array();
        $aopCompositeCollectionPropModel = \Loris\Discovery::find(
            $complexResources[0]->arrayOfObjectPropTemplate->aopCompositeCollectionProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfObjectProp as $item) {
                if ($item->aopCompositeCollectionProp instanceof $aopCompositeCollectionPropModel->class) {
                    $aopCompositeCollectionProps[] = $item->aopCompositeCollectionProp;
                }
            }
        }
        if (!empty($aopCompositeCollectionProps)) {
            call_user_func(
                array($aopCompositeCollectionPropModel->class, 'query'),
                $aopCompositeCollectionProps
            );
        }

        $aopCompositeResourceProps = array();
        $aopCompositeResourcePropModel = \Loris\Discovery::find(
            $complexResources[0]->arrayOfObjectPropTemplate->aopCompositeResourceProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfObjectProp as $item) {
                if ($item->aopCompositeResourceProp instanceof $aopCompositeResourcePropModel->class) {
                    $aopCompositeResourceProps[] = $item->aopCompositeResourceProp;
                }
            }
        }
        if (!empty($aopCompositeResourceProps)) {
            call_user_func(
                array($aopCompositeResourcePropModel->class, 'query'),
                $aopCompositeResourceProps
            );
        }

        $aopResourceProps = array();
        $aopResourcePropModel = \Loris\Discovery::find(
            $complexResources[0]->arrayOfObjectPropTemplate->aopResourceProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfObjectProp as $item) {
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
            $complexResources[0]->arrayOfResourcePropTemplate->uri()
        );
        foreach ($complexResources as $complexResource) {
            foreach ($complexResource->arrayOfResourceProp as $item) {
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
            $complexResources[0]->collectionProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->collectionProp instanceof $collectionPropModel->class) {
                $collectionProps[] = $complexResource->collectionProp;
            }
        }
        if (!empty($collectionProps)) {
            call_user_func(
                array($collectionPropModel->class, 'query'),
                $collectionProps
            );
        }

        $compositeCollectionProps = array();
        $compositeCollectionPropModel = \Loris\Discovery::find(
            $complexResources[0]->compositeCollectionProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->compositeCollectionProp instanceof $compositeCollectionPropModel->class) {
                $compositeCollectionProps[] = $complexResource->compositeCollectionProp;
            }
        }
        if (!empty($compositeCollectionProps)) {
            call_user_func(
                array($compositeCollectionPropModel->class, 'query'),
                $compositeCollectionProps
            );
        }

        $compositeResourceProps = array();
        $compositeResourcePropModel = \Loris\Discovery::find(
            $complexResources[0]->compositeResourceProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->compositeResourceProp instanceof $compositeResourcePropModel->class) {
                $compositeResourceProps[] = $complexResource->compositeResourceProp;
            }
        }
        if (!empty($compositeResourceProps)) {
            call_user_func(
                array($compositeResourcePropModel->class, 'query'),
                $compositeResourceProps
            );
        }

        $opCollectionProps = array();
        $opCollectionPropModel = \Loris\Discovery::find(
            $complexResources[0]->objectProp->opCollectionProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->objectProp->opCollectionProp instanceof $opCollectionPropModel->class) {
                $opCollectionProps[] = $complexResource->objectProp->opCollectionProp;
            }
        }
        if (!empty($opCollectionProps)) {
            call_user_func(
                array($opCollectionPropModel->class, 'query'),
                $opCollectionProps
            );
        }

        $opCompositeCollectionProps = array();
        $opCompositeCollectionPropModel = \Loris\Discovery::find(
            $complexResources[0]->objectProp->opCompositeCollectionProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->objectProp->opCompositeCollectionProp instanceof $opCompositeCollectionPropModel->class) {
                $opCompositeCollectionProps[] = $complexResource->objectProp->opCompositeCollectionProp;
            }
        }
        if (!empty($opCompositeCollectionProps)) {
            call_user_func(
                array($opCompositeCollectionPropModel->class, 'query'),
                $opCompositeCollectionProps
            );
        }

        $opCompositeResourceProps = array();
        $opCompositeResourcePropModel = \Loris\Discovery::find(
            $complexResources[0]->objectProp->opCompositeResourceProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->objectProp->opCompositeResourceProp instanceof $opCompositeResourcePropModel->class) {
                $opCompositeResourceProps[] = $complexResource->objectProp->opCompositeResourceProp;
            }
        }
        if (!empty($opCompositeResourceProps)) {
            call_user_func(
                array($opCompositeResourcePropModel->class, 'query'),
                $opCompositeResourceProps
            );
        }

        $opResourceProps = array();
        $opResourcePropModel = \Loris\Discovery::find(
            $complexResources[0]->objectProp->opResourceProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->objectProp->opResourceProp instanceof $opResourcePropModel->class) {
                $opResourceProps[] = $complexResource->objectProp->opResourceProp;
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
            $complexResources[0]->resourceProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->resourceProp instanceof $resourcePropModel->class) {
                $resourceProps[] = $complexResource->resourceProp;
            }
        }
        if (!empty($resourceProps)) {
            call_user_func(
                array($resourcePropModel->class, 'query'),
                $resourceProps
            );
        }

        $topLevelResourceProps = array();
        $topLevelResourcePropModel = \Loris\Discovery::find(
            $complexResources[0]->topLevelResourceProp->uri()
        );
        foreach ($complexResources as $complexResource) {
            if ($complexResource->topLevelResourceProp instanceof $topLevelResourcePropModel->class) {
                $topLevelResourceProps[] = $complexResource->topLevelResourceProp;
            }
        }
        if (!empty($topLevelResourceProps)) {
            call_user_func(
                array($topLevelResourcePropModel->class, 'query'),
                $topLevelResourceProps
            );
        }

    }

    /**
     *
     * @param \stdClass $results
     */
    public function fromResults(\stdClass $results)
    {
        foreach ($results->arrayOfCollectionProp as $item) {
            assert('\Loris\Utility::isString($item, \'simpleCollectionId\') /* resource id must be a string */');
            assert('\Loris\Utility::isNumber($item, \'simpleCollectionTotal\') /* simpleCollectionTotal must be a number */');
            if ($item->simpleCollectionId != null) {
                $simpleCollection = clone $this->arrayOfCollectionPropTemplate;
                
                $simpleCollection->id = $item->simpleCollectionId;
                $simpleCollection->meta->total = intval(
                    $item->simpleCollectionTotal
                );
                $simpleCollection->updateMetaUri();
                $this->arrayOfCollectionProp[] = $simpleCollection;
            }
        }

        foreach ($results->arrayOfCompositeCollectionProp as $item) {
            assert('\Loris\Utility::isString($item, \'compositeCollectionIdLeft\') /* resource id must be a string */');
            assert('\Loris\Utility::isString($item, \'compositeCollectionIdRight\') /* resource id must be a string */');
            assert('\Loris\Utility::isNumber($item, \'compositeCollectionTotal\') /* compositeCollectionTotal must be a number */');
            if ($item->compositeCollectionIdLeft != null && 
                $item->compositeCollectionIdRight != null) {
                $compositeCollection = clone $this->arrayOfCompositeCollectionPropTemplate;
                
                $compositeCollection->idLeft = $item->compositeCollectionIdLeft;
                $compositeCollection->idRight = $item->compositeCollectionIdRight;
                $compositeCollection->meta->total = intval(
                    $item->compositeCollectionTotal
                );
                $compositeCollection->updateMetaUri();
                $this->arrayOfCompositeCollectionProp[] = $compositeCollection;
            }
        }

        foreach ($results->arrayOfCompositeResourceProp as $item) {
            assert('\Loris\Utility::isString($item, \'compositeResourceIdLeft\') /* resource id must be a string */');
            assert('\Loris\Utility::isString($item, \'compositeResourceIdRight\') /* resource id must be a string */');
            if ($item->compositeResourceIdLeft != null && 
                $item->compositeResourceIdRight != null) {
                $compositeResource = clone $this->arrayOfCompositeResourcePropTemplate;
            
                $compositeResource->idLeft = $item->compositeResourceIdLeft;
                $compositeResource->idRight = $item->compositeResourceIdRight;
                $compositeResource->updateMetaUri();
                $this->arrayOfCompositeResourceProp[] = $compositeResource;
            }
        }

        foreach ($results->arrayOfDateProp as $item) {
            assert('\Loris\Utility::isDate($item) /* property must be in date format */');
            $this->arrayOfDateProp[] = \DateTime::createFromFormat(
                'Y-m-d', 
                $item
            ) ?: null;
        }

        foreach ($results->arrayOfNumberProp as $item) {
            assert('\Loris\Utility::isNumber($item) /* property must be a number */');
            $this->arrayOfNumberProp[] = intval($item);
        }

        $arrayOfObjectPropCount = count($results->arrayOfObjectProp);
        for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
            $this->arrayOfObjectProp[$i] = clone $this->arrayOfObjectPropTemplate;

            assert('\Loris\Utility::isBool($results->arrayOfObjectProp[$i], \'aopBoolProp\') /* property must be a boolean */');
            $this->arrayOfObjectProp[$i]->aopBoolProp = boolval(
                $results->arrayOfObjectProp[$i]->aopBoolProp
            );

            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i], \'aopCollectionPropId\') /* collection id must be a string */');
            assert('\Loris\Utility::isNumber($results->arrayOfObjectProp[$i], \'aopCollectionPropTotal\') /* collection total must be a number */');
            if ($results->arrayOfObjectProp[$i]->aopCollectionPropId != null) {
            
                $this->arrayOfObjectProp[$i]->aopCollectionProp->id = $results->arrayOfObjectProp[$i]->aopCollectionPropId;
                $this->arrayOfObjectProp[$i]->aopCollectionProp->meta->total = intval(
                    $results->arrayOfObjectProp[$i]->aopCollectionPropTotal
                );
                $this->arrayOfObjectProp[$i]->aopCollectionProp->updateMetaUri();
            } else {
                $this->arrayOfObjectProp[$i]->aopCollectionProp = new NullResource(
                    $this->arrayOfObjectProp[$i]->aopCollectionProp->uri()
                );
            }

            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i], \'aopCompositeCollectionPropIdLeft\') /* collection id must be a string */');
            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i], \'aopCompositeCollectionPropIdRight\') /* collection id must be a string */');
            assert('\Loris\Utility::isNumber($results->arrayOfObjectProp[$i], \'aopCompositeCollectionPropTotal\') /* collection total must be a number */');
            if ($results->arrayOfObjectProp[$i]->aopCompositeCollectionPropIdLeft != null && 
                $results->arrayOfObjectProp[$i]->aopCompositeCollectionPropIdRight != null) {
            
                $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->idLeft = $results->arrayOfObjectProp[$i]->aopCompositeCollectionPropIdLeft;
                $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->idRight = $results->arrayOfObjectProp[$i]->aopCompositeCollectionPropIdRight;
                $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->meta->total = intval(
                    $results->arrayOfObjectProp[$i]->aopCompositeCollectionPropTotal
                );
                $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->updateMetaUri();
            } else {
                $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp = new NullResource(
                    $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->uri()
                );
            }

            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i], \'aopCompositeResourcePropIdLeft\') /* resource id must be a string */');
            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i], \'aopCompositeResourcePropIdRight\') /* resource id must be a string */');
            if ($results->arrayOfObjectProp[$i]->aopCompositeResourcePropIdLeft != null && 
                $results->arrayOfObjectProp[$i]->aopCompositeResourcePropIdRight != null) {
            
                $this->arrayOfObjectProp[$i]->aopCompositeResourceProp->idLeft = $results->arrayOfObjectProp[$i]->aopCompositeResourcePropIdLeft;
                $this->arrayOfObjectProp[$i]->aopCompositeResourceProp->idRight = $results->arrayOfObjectProp[$i]->aopCompositeResourcePropIdRight;
                $this->arrayOfObjectProp[$i]->aopCompositeResourceProp->updateMetaUri();
            } else {
                $this->arrayOfObjectProp[$i]->aopCompositeResourceProp = new NullResource(
                    $this->arrayOfObjectProp[$i]->aopCompositeResourceProp->uri()
                );
            }

   
            assert('\Loris\Utility::isDate($results->arrayOfObjectProp[$i], \'aopDateProp\') /* property must be in date format */');
            $this->arrayOfObjectProp[$i]->aopDateProp = \DateTime::createFromFormat(
                'Y-m-d', 
                $results->arrayOfObjectProp[$i]->aopDateProp
            ) ?: null;

            assert('\Loris\Utility::isNumber($results->arrayOfObjectProp[$i], \'aopNumberProp\') /* property must be a number */');
            $this->arrayOfObjectProp[$i]->aopNumberProp = intval(
                $results->arrayOfObjectProp[$i]->aopNumberProp
            );

            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i], \'aopResourcePropId\') /* resource id must be a string */');
            if ($results->arrayOfObjectProp[$i]->aopResourcePropId != null) {
            
                $this->arrayOfObjectProp[$i]->aopResourceProp->id = $results->arrayOfObjectProp[$i]->aopResourcePropId;
                $this->arrayOfObjectProp[$i]->aopResourceProp->updateMetaUri();
            } else {
                $this->arrayOfObjectProp[$i]->aopResourceProp = new NullResource(
                    $this->arrayOfObjectProp[$i]->aopResourceProp->uri()
                );
            }

            assert('\Loris\Utility::isString($results->arrayOfObjectProp[$i], \'aopStringProp\') /* property must be a string */');
            $this->arrayOfObjectProp[$i]->aopStringProp = $results->arrayOfObjectProp[$i]->aopStringProp;

        }

        foreach ($results->arrayOfResourceProp as $item) {
            assert('\Loris\Utility::isString($item, \'simpleResourceId\') /* resource id must be a string */');
            if ($item->simpleResourceId != null) {
                $simpleResource = clone $this->arrayOfResourcePropTemplate;
            
                $simpleResource->id = $item->simpleResourceId;
                $simpleResource->updateMetaUri();
                $this->arrayOfResourceProp[] = $simpleResource;
            }
        }

        foreach ($results->arrayOfStringProp as $item) {
            assert('\Loris\Utility::isString($item) /* property must be a string */');
            $this->arrayOfStringProp[] = $item;
        }

        assert('\Loris\Utility::isBool($results, \'boolProp\') /* property must be a boolean */');
        $this->boolProp = boolval(
            $results->boolProp
        );

        assert('\Loris\Utility::isString($results, \'collectionPropId\') /* collection id must be a string */');
        assert('\Loris\Utility::isNumber($results, \'collectionPropTotal\') /* collection total must be a number */');
        if ($results->collectionPropId != null) {
        
            $this->collectionProp->id = $results->collectionPropId;
            $this->collectionProp->meta->total = intval(
                $results->collectionPropTotal
            );
            $this->collectionProp->updateMetaUri();
        } else {
            $this->collectionProp = new NullResource(
                $this->collectionProp->uri()
            );
        }

        assert('\Loris\Utility::isString($results, \'compositeCollectionPropIdLeft\') /* collection id must be a string */');
        assert('\Loris\Utility::isString($results, \'compositeCollectionPropIdRight\') /* collection id must be a string */');
        assert('\Loris\Utility::isNumber($results, \'compositeCollectionPropTotal\') /* collection total must be a number */');
        if ($results->compositeCollectionPropIdLeft != null && 
            $results->compositeCollectionPropIdRight != null) {
        
            $this->compositeCollectionProp->idLeft = $results->compositeCollectionPropIdLeft;
            $this->compositeCollectionProp->idRight = $results->compositeCollectionPropIdRight;
            $this->compositeCollectionProp->meta->total = intval(
                $results->compositeCollectionPropTotal
            );
            $this->compositeCollectionProp->updateMetaUri();
        } else {
            $this->compositeCollectionProp = new NullResource(
                $this->compositeCollectionProp->uri()
            );
        }

        assert('\Loris\Utility::isString($results, \'compositeResourcePropIdLeft\') /* resource id must be a string */');
        assert('\Loris\Utility::isString($results, \'compositeResourcePropIdRight\') /* resource id must be a string */');
        if ($results->compositeResourcePropIdLeft != null && 
            $results->compositeResourcePropIdRight != null) {
        
            $this->compositeResourceProp->idLeft = $results->compositeResourcePropIdLeft;
            $this->compositeResourceProp->idRight = $results->compositeResourcePropIdRight;
            $this->compositeResourceProp->updateMetaUri();
        } else {
            $this->compositeResourceProp = new NullResource(
                $this->compositeResourceProp->uri()
            );
        }

        assert('\Loris\Utility::isDate($results, \'dateProp\') /* property must be in date format */');
        $this->dateProp = \DateTime::createFromFormat(
            'Y-m-d', 
            $results->dateProp
        ) ?: null;

        assert('\Loris\Utility::isString($results, \'idLeft\') /* property must be a string */');
        $this->idLeft = $results->idLeft;

        assert('\Loris\Utility::isString($results, \'idRight\') /* property must be a string */');
        $this->idRight = $results->idRight;

        assert('\Loris\Utility::isNumber($results, \'numberProp\') /* property must be a number */');
        $this->numberProp = intval(
            $results->numberProp
        );

        assert('\Loris\Utility::isBool($results->objectProp, \'opBoolProp\') /* property must be a boolean */');
        $this->objectProp->opBoolProp = boolval(
            $results->objectProp->opBoolProp
        );

        assert('\Loris\Utility::isString($results->objectProp, \'opCollectionPropId\') /* resource id must be a string */');
        assert('\Loris\Utility::isNumber($results->objectProp, \'opCollectionPropTotal\') /* collection total must be a number */');
        if ($results->objectProp->opCollectionPropId != null) {
        
            $this->objectProp->opCollectionProp->id = $results->objectProp->opCollectionPropId;
            $this->objectProp->opCollectionProp->meta->total = intval(
                $results->objectProp->opCollectionPropTotal
            );
            $this->objectProp->opCollectionProp->updateMetaUri();
        } else {
            $this->objectProp->opCollectionProp = new NullResource(
                $this->objectProp->opCollectionProp->uri()
            );
        }

        assert('\Loris\Utility::isString($results->objectProp, \'opCompositeCollectionPropIdLeft\') /* resource id must be a string */');
        assert('\Loris\Utility::isString($results->objectProp, \'opCompositeCollectionPropIdRight\') /* resource id must be a string */');
        assert('\Loris\Utility::isNumber($results->objectProp, \'opCompositeCollectionPropTotal\') /* collection total must be a number */');
        if ($results->objectProp->opCompositeCollectionPropIdLeft != null && 
            $results->objectProp->opCompositeCollectionPropIdRight != null) {
        
            $this->objectProp->opCompositeCollectionProp->idLeft = $results->objectProp->opCompositeCollectionPropIdLeft;
            $this->objectProp->opCompositeCollectionProp->idRight = $results->objectProp->opCompositeCollectionPropIdRight;
            $this->objectProp->opCompositeCollectionProp->meta->total = intval(
                $results->objectProp->opCompositeCollectionPropTotal
            );
            $this->objectProp->opCompositeCollectionProp->updateMetaUri();
        } else {
            $this->objectProp->opCompositeCollectionProp = new NullResource(
                $this->objectProp->opCompositeCollectionProp->uri()
            );
        }

        assert('\Loris\Utility::isString($results->objectProp, \'opCompositeResourcePropIdLeft\') /* resource id must be a string */');
        assert('\Loris\Utility::isString($results->objectProp, \'opCompositeResourcePropIdRight\') /* resource id must be a string */');
        if ($results->objectProp->opCompositeResourcePropIdLeft != null && 
            $results->objectProp->opCompositeResourcePropIdRight != null) {
        
            $this->objectProp->opCompositeResourceProp->idLeft = $results->objectProp->opCompositeResourcePropIdLeft;
            $this->objectProp->opCompositeResourceProp->idRight = $results->objectProp->opCompositeResourcePropIdRight;
            $this->objectProp->opCompositeResourceProp->updateMetaUri();
        } else {
            $this->objectProp->opCompositeResourceProp = new NullResource(
                $this->objectProp->opCompositeResourceProp->uri()
            );
        }

   
        assert('\Loris\Utility::isDate($results->objectProp, \'opDateProp\') /* property must be in date format */');
        $this->objectProp->opDateProp = \DateTime::createFromFormat(
            'Y-m-d', 
            $results->objectProp->opDateProp
        ) ?: null;

        assert('\Loris\Utility::isNumber($results->objectProp, \'opNumberProp\') /* property must be a number */');
        $this->objectProp->opNumberProp = intval(
            $results->objectProp->opNumberProp
        );

        assert('\Loris\Utility::isString($results->objectProp, \'opResourcePropId\') /* resource id must be a string */');
        if ($results->objectProp->opResourcePropId != null) {
        
            $this->objectProp->opResourceProp->id = $results->objectProp->opResourcePropId;
            $this->objectProp->opResourceProp->updateMetaUri();
        } else {
            $this->objectProp->opResourceProp = new NullResource(
                $this->objectProp->opResourceProp->uri()
            );
        }

        assert('\Loris\Utility::isString($results->objectProp, \'opStringProp\') /* property must be a string */');
        $this->objectProp->opStringProp = $results->objectProp->opStringProp;

        assert('\Loris\Utility::isString($results, \'resourcePropId\') /* resource id must be a string */');
        if ($results->resourcePropId != null) {
        
            $this->resourceProp->id = $results->resourcePropId;
            $this->resourceProp->updateMetaUri();
        } else {
            $this->resourceProp = new NullResource(
                $this->resourceProp->uri()
            );
        }

        assert('\Loris\Utility::isString($results, \'stringProp\') /* property must be a string */');
        $this->stringProp = $results->stringProp;

        if ($results->topLevelResourceProp != null) {
        
            $this->topLevelResourceProp->updateMetaUri();
        } else {
            $this->topLevelResourceProp = new NullResource(
                $this->topLevelResourceProp->uri()
            );
        }

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
                $this->arrayOfCollectionProp[0]->uri()
            );
            $arrayOfCollectionPropExpanded = is_array(
                $this->expansions['arrayOfCollectionProp']
            );
            $arrayOfCollectionPropCount = count($this->arrayOfCollectionProp);
            for ($i = 0; $i < $arrayOfCollectionPropCount; $i++) {
                $this->arrayOfCollectionProp[$i] = new $arrayOfCollectionPropModel->class(
                    $this->arrayOfCollectionProp[$i]->ids(),
                    $this->arrayOfCollectionProp[$i]->meta->page,
                    $this->arrayOfCollectionProp[$i]->meta->limit
                );

                if ($arrayOfCollectionPropExpanded) {
                    $this->arrayOfCollectionProp[$i]->expand(
                        $this->expansions['arrayOfCollectionProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfCompositeCollectionProp', $this->expansions) &&
            count($this->arrayOfCompositeCollectionProp) > 0) {

            $arrayOfCompositeCollectionPropModel = \Loris\Discovery::find(
                $this->arrayOfCompositeCollectionProp[0]->uri()
            );
            $arrayOfCompositeCollectionPropExpanded = is_array(
                $this->expansions['arrayOfCompositeCollectionProp']
            );
            $arrayOfCompositeCollectionPropCount = count($this->arrayOfCompositeCollectionProp);
            for ($i = 0; $i < $arrayOfCompositeCollectionPropCount; $i++) {
                $this->arrayOfCompositeCollectionProp[$i] = new $arrayOfCompositeCollectionPropModel->class(
                    $this->arrayOfCompositeCollectionProp[$i]->ids(),
                    $this->arrayOfCompositeCollectionProp[$i]->meta->page,
                    $this->arrayOfCompositeCollectionProp[$i]->meta->limit
                );

                if ($arrayOfCompositeCollectionPropExpanded) {
                    $this->arrayOfCompositeCollectionProp[$i]->expand(
                        $this->expansions['arrayOfCompositeCollectionProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfCompositeResourceProp', $this->expansions) &&
            count($this->arrayOfCompositeResourceProp) > 0) {

            $arrayOfCompositeResourcePropModel = \Loris\Discovery::find(
                $this->arrayOfCompositeResourceProp[0]->uri()
            );
            $arrayOfCompositeResourcePropExpanded = is_array(
                $this->expansions['arrayOfCompositeResourceProp']
            );
            $arrayOfCompositeResourcePropCount = count($this->arrayOfCompositeResourceProp);
            for ($i = 0; $i < $arrayOfCompositeResourcePropCount; $i++) {
                $this->arrayOfCompositeResourceProp[$i] = new $arrayOfCompositeResourcePropModel->class(
                    $this->arrayOfCompositeResourceProp[$i]->ids()
                );

                if ($arrayOfCompositeResourcePropExpanded) {
                    $this->arrayOfCompositeResourceProp[$i]->expand(
                        $this->expansions['arrayOfCompositeResourceProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfObjectProp', $this->expansions) &&
            array_key_exists('aopCollectionProp', $this->expansions['arrayOfObjectProp']) &&
            count($this->arrayOfObjectProp) > 0) {

            $aopCollectionPropModel = \Loris\Discovery::find(
                $this->arrayOfObjectProp[0]->aopCollectionProp->uri()
            );
            $aopCollectionPropExpanded = is_array(
                $this->expansions['arrayOfObjectProp']['aopCollectionProp']
            );
            $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
            for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
                $this->arrayOfObjectProp[$i]->aopCollectionProp  = new $aopCollectionPropModel->class(
                    $this->arrayOfObjectProp[$i]->aopCollectionProp->ids(),
                    $this->arrayOfObjectProp[$i]->aopCollectionProp->page,
                    $this->arrayOfObjectProp[$i]->aopCollectionProp->limit
                );

                if ($aopCollectionPropExpanded) {
                    $this->arrayOfObjectProp[$i]->aopCollectionProp->expand(
                        $this->expansions['arrayOfObjectProp']['aopCollectionProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfObjectProp', $this->expansions) &&
            array_key_exists('aopCompositeCollectionProp', $this->expansions['arrayOfObjectProp']) &&
            count($this->arrayOfObjectProp) > 0) {

            $aopCompositeCollectionPropModel = \Loris\Discovery::find(
                $this->arrayOfObjectProp[0]->aopCompositeCollectionProp->uri()
            );
            $aopCompositeCollectionPropExpanded = is_array(
                $this->expansions['arrayOfObjectProp']['aopCompositeCollectionProp']
            );
            $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
            for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
                $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp  = new $aopCompositeCollectionPropModel->class(
                    $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->ids(),
                    $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->page,
                    $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->limit
                );

                if ($aopCompositeCollectionPropExpanded) {
                    $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->expand(
                        $this->expansions['arrayOfObjectProp']['aopCompositeCollectionProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfObjectProp', $this->expansions) &&
            array_key_exists('aopCompositeResourceProp', $this->expansions['arrayOfObjectProp']) &&
            count($this->arrayOfObjectProp) > 0) {

            $aopCompositeResourcePropModel = \Loris\Discovery::find(
                $this->arrayOfObjectProp[0]->aopCompositeResourceProp->uri()
            );
            $aopCompositeResourcePropExpanded = is_array(
                $this->expansions['arrayOfObjectProp']['aopCompositeResourceProp']
            );
            $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
            for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
                $this->arrayOfObjectProp[$i]->aopCompositeResourceProp  = new $aopCompositeResourcePropModel->class(
                    $this->arrayOfObjectProp[$i]->aopCompositeResourceProp->ids()
                );

                if ($aopCompositeResourcePropExpanded) {
                    $this->arrayOfObjectProp[$i]->aopCompositeResourceProp->expand(
                        $this->expansions['arrayOfObjectProp']['aopCompositeResourceProp']
                    );
                }
            }
        }

        if (array_key_exists('arrayOfObjectProp', $this->expansions) &&
            array_key_exists('aopResourceProp', $this->expansions['arrayOfObjectProp']) &&
            count($this->arrayOfObjectProp) > 0) {

            $aopResourcePropModel = \Loris\Discovery::find(
                $this->arrayOfObjectProp[0]->aopResourceProp->uri()
            );
            $aopResourcePropExpanded = is_array(
                $this->expansions['arrayOfObjectProp']['aopResourceProp']
            );
            $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
            for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
                $this->arrayOfObjectProp[$i]->aopResourceProp  = new $aopResourcePropModel->class(
                    $this->arrayOfObjectProp[$i]->aopResourceProp->ids()
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
                $this->arrayOfResourceProp[0]->uri()
            );
            $arrayOfResourcePropExpanded = is_array(
                $this->expansions['arrayOfResourceProp']
            );
            $arrayOfResourcePropCount = count($this->arrayOfResourceProp);
            for ($i = 0; $i < $arrayOfResourcePropCount; $i++) {
                $this->arrayOfResourceProp[$i] = new $arrayOfResourcePropModel->class(
                    $this->arrayOfResourceProp[$i]->ids()
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
                $this->collectionProp->uri()
            );
            $this->collectionProp = new $collectionPropModel->class(
                $this->collectionProp->ids(),
                $this->collectionProp->meta->page,
                $this->collectionProp->meta->limit
            );

            if (is_array($this->expansions['collectionProp'])) {
                $this->collectionProp->expand($this->expansions['collectionProp']);
            }
        }

        if (array_key_exists('compositeCollectionProp', $this->expansions)) {

            $compositeCollectionPropModel = \Loris\Discovery::find(
                $this->compositeCollectionProp->uri()
            );
            $this->compositeCollectionProp = new $compositeCollectionPropModel->class(
                $this->compositeCollectionProp->ids(),
                $this->compositeCollectionProp->meta->page,
                $this->compositeCollectionProp->meta->limit
            );

            if (is_array($this->expansions['compositeCollectionProp'])) {
                $this->compositeCollectionProp->expand($this->expansions['compositeCollectionProp']);
            }
        }

        if (array_key_exists('compositeResourceProp', $this->expansions)) {

            $compositeResourcePropModel = \Loris\Discovery::find(
                $this->compositeResourceProp->uri()
            );
            $this->compositeResourceProp = new $compositeResourcePropModel->class(
                $this->compositeResourceProp->ids()
            );

            if (is_array($this->expansions['compositeResourceProp'])) {
                $this->compositeResourceProp->expand($this->expansions['compositeResourceProp']);
            }
        }

        if (array_key_exists('objectProp', $this->expansions) &&
            array_key_exists('opCollectionProp', $this->expansions['objectProp'])) {

            $opCollectionPropModel = \Loris\Discovery::find(
                $this->objectProp->opCollectionProp->uri()
            );
            $this->objectProp->opCollectionProp = new $opCollectionPropModel->class(
                $this->objectProp->opCollectionProp->ids(),
                $this->objectProp->opCollectionProp->meta->page,
                $this->objectProp->opCollectionProp->meta->limit
            );

            if (is_array($this->expansions['objectProp']['opCollectionProp'])) {
                $this->objectProp->opCollectionProp->expand(
                    $this->expansions['objectProp']['opCollectionProp']
                );
            }
        }

        if (array_key_exists('objectProp', $this->expansions) &&
            array_key_exists('opCompositeCollectionProp', $this->expansions['objectProp'])) {

            $opCompositeCollectionPropModel = \Loris\Discovery::find(
                $this->objectProp->opCompositeCollectionProp->uri()
            );
            $this->objectProp->opCompositeCollectionProp = new $opCompositeCollectionPropModel->class(
                $this->objectProp->opCompositeCollectionProp->ids(),
                $this->objectProp->opCompositeCollectionProp->meta->page,
                $this->objectProp->opCompositeCollectionProp->meta->limit
            );

            if (is_array($this->expansions['objectProp']['opCompositeCollectionProp'])) {
                $this->objectProp->opCompositeCollectionProp->expand(
                    $this->expansions['objectProp']['opCompositeCollectionProp']
                );
            }
        }

        if (array_key_exists('objectProp', $this->expansions) &&
            array_key_exists('opCompositeResourceProp', $this->expansions['objectProp'])) {

            $opCompositeResourcePropModel = \Loris\Discovery::find(
                $this->objectProp->opCompositeResourceProp->uri()
            );
            $this->objectProp->opCompositeResourceProp = new $opCompositeResourcePropModel->class(
                $this->objectProp->opCompositeResourceProp->ids()
            );

            if (is_array($this->expansions['objectProp']['opCompositeResourceProp'])) {
                $this->objectProp->opCompositeResourceProp->expand(
                    $this->expansions['objectProp']['opCompositeResourceProp']
                );
            }
        }

        if (array_key_exists('objectProp', $this->expansions) &&
            array_key_exists('opResourceProp', $this->expansions['objectProp'])) {

            $opResourcePropModel = \Loris\Discovery::find(
                $this->objectProp->opResourceProp->uri()
            );
            $this->objectProp->opResourceProp = new $opResourcePropModel->class(
                $this->objectProp->opResourceProp->ids()
            );

            if (is_array($this->expansions['objectProp']['opResourceProp'])) {
                $this->objectProp->opResourceProp->expand(
                    $this->expansions['objectProp']['opResourceProp']
                );
            }
        }

        if (array_key_exists('resourceProp', $this->expansions)) {

            $resourcePropModel = \Loris\Discovery::find(
                $this->resourceProp->uri()
            );
            $this->resourceProp = new $resourcePropModel->class(
                $this->resourceProp->ids()
            );

            if (is_array($this->expansions['resourceProp'])) {
                $this->resourceProp->expand($this->expansions['resourceProp']);
            }
        }

        if (array_key_exists('topLevelResourceProp', $this->expansions)) {

            $topLevelResourcePropModel = \Loris\Discovery::find(
                $this->topLevelResourceProp->uri()
            );
            $this->topLevelResourceProp = new $topLevelResourcePropModel->class(
                $this->topLevelResourceProp->ids()
            );

            if (is_array($this->expansions['topLevelResourceProp'])) {
                $this->topLevelResourceProp->expand($this->expansions['topLevelResourceProp']);
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

        $serialized->arrayOfCompositeCollectionProp = array();
        $arrayOfCompositeCollectionPropCount = count($this->arrayOfCompositeCollectionProp);
        for ($i = 0; $i < $arrayOfCompositeCollectionPropCount; $i++) {
            $serialized->arrayOfCompositeCollectionProp[] = $this->arrayOfCompositeCollectionProp[$i]->serialize();
        }

        $serialized->arrayOfCompositeResourceProp = array();
        $arrayOfCompositeResourcePropCount = count($this->arrayOfCompositeResourceProp);
        for ($i = 0; $i < $arrayOfCompositeResourcePropCount; $i++) {
            $serialized->arrayOfCompositeResourceProp[] = $this->arrayOfCompositeResourceProp[$i]->serialize();
        }

        $serialized->arrayOfDateProp = array();
        $arrayOfDatePropCount = count($this->arrayOfDateProp);
        for ($i = 0; $i < $arrayOfDatePropCount; $i++) {
            if ($this->arrayOfDateProp[$i] !== null) {
                $serialized->arrayOfDateProp[$i] = $this->arrayOfDateProp[$i]->format('Y-m-d');
            } else {
                $serialized->arrayOfDateProp[$i] = null;
            }
        }

        $serialized->arrayOfNumberProp = $this->arrayOfNumberProp;

        $serialized->arrayOfObjectProp = array();
        $arrayOfObjectPropCount = count($this->arrayOfObjectProp);
        for ($i = 0; $i < $arrayOfObjectPropCount; $i++) {
            $serialized->arrayOfObjectProp[$i] = new \stdClass;
            $serialized->arrayOfObjectProp[$i]->aopBoolProp = $this->arrayOfObjectProp[$i]->aopBoolProp;
            $serialized->arrayOfObjectProp[$i]->aopCollectionProp = $this->arrayOfObjectProp[$i]->aopCollectionProp->serialize();
            $serialized->arrayOfObjectProp[$i]->aopCompositeCollectionProp = $this->arrayOfObjectProp[$i]->aopCompositeCollectionProp->serialize();
            $serialized->arrayOfObjectProp[$i]->aopCompositeResourceProp = $this->arrayOfObjectProp[$i]->aopCompositeResourceProp->serialize();
            if ($this->arrayOfObjectProp[$i]->aopDateProp !== null) {
                $serialized->arrayOfObjectProp[$i]->aopDateProp = $this->arrayOfObjectProp[$i]->aopDateProp->format('Y-m-d');
            } else {
                $serialized->arrayOfObjectProp[$i]->aopDateProp = null;
            }
            $serialized->arrayOfObjectProp[$i]->aopNumberProp = $this->arrayOfObjectProp[$i]->aopNumberProp;
            $serialized->arrayOfObjectProp[$i]->aopResourceProp = $this->arrayOfObjectProp[$i]->aopResourceProp->serialize();
            $serialized->arrayOfObjectProp[$i]->aopStringProp = $this->arrayOfObjectProp[$i]->aopStringProp;
        }

        $serialized->arrayOfResourceProp = array();
        $arrayOfResourcePropCount = count($this->arrayOfResourceProp);
        for ($i = 0; $i < $arrayOfResourcePropCount; $i++) {
            $serialized->arrayOfResourceProp[] = $this->arrayOfResourceProp[$i]->serialize();
        }

        $serialized->arrayOfStringProp = $this->arrayOfStringProp;

        $serialized->boolProp = $this->boolProp;

        $serialized->collectionProp = $this->collectionProp->serialize();

        $serialized->compositeCollectionProp = $this->compositeCollectionProp->serialize();

        $serialized->compositeResourceProp = $this->compositeResourceProp->serialize();

        if ($this->dateProp !== null) {
            $serialized->dateProp = $this->dateProp->format('Y-m-d');
        } else {
            $serialized->dateProp = null;
        }

        $serialized->idLeft = $this->idLeft;

        $serialized->idRight = $this->idRight;

        $serialized->numberProp = $this->numberProp;

        $serialized->objectProp = new \stdClass;
        $serialized->objectProp->opBoolProp = $this->objectProp->opBoolProp;
        $serialized->objectProp->opCollectionProp = $this->objectProp->opCollectionProp->serialize();
        $serialized->objectProp->opCompositeCollectionProp = $this->objectProp->opCompositeCollectionProp->serialize();
        $serialized->objectProp->opCompositeResourceProp = $this->objectProp->opCompositeResourceProp->serialize();
        if ($this->objectProp->opDateProp !== null) {
            $serialized->objectProp->opDateProp = $this->objectProp->opDateProp->format('Y-m-d');
        } else {
            $serialized->objectProp->opDateProp = null;
        }
        $serialized->objectProp->opNumberProp = $this->objectProp->opNumberProp;
        $serialized->objectProp->opResourceProp = $this->objectProp->opResourceProp->serialize();
        $serialized->objectProp->opStringProp = $this->objectProp->opStringProp;

        $serialized->resourceProp = $this->resourceProp->serialize();

        $serialized->stringProp = $this->stringProp;

        $serialized->topLevelResourceProp = $this->topLevelResourceProp->serialize();

        return $serialized;
    }
}
