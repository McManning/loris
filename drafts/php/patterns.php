<?

// Better patterning...

// constructor Patterns
{
    // Resource property
    $this->resourceProperty = new Meta(
        null, 
        '/resource/{id}'
    );

    // Collection property
    $this->collectionProperty = new MetaCollection(
        null,
        '/collection/{id}'
    );

    // object property
    $this->objectProperty = new \stdClass;

    // object property - resource property
    $this->objectProperty->resourceProperty = new Meta(
        null,
        '/resource/{id}'
    );

    // object property - collection property
    $this->objectProperty->collectionProperty = new MetaCollection(
        null,
        '/collection/{id}'
    );

    // array property of resource
    $this->arrayPropertyPattern = new Meta(
        null,
        '/resource/{id}'
    );

    // array property of collection
    $this->arrayPropertyPattern = new MetaCollection(
        null,
        '/resource/{id}'
    );

    // array property of object
    $this->arrayPropertyPattern = new \stdClass;

    // array property of object with resource property
    $this->arrayPropertyPattern->resourceProperty = new Meta(
        null,
        '/resource/{id}'
    );

    // array property of object with collection property
    $this->arrayPropertyPattern->collectionProperty = new MetaCollection(
        null,
        '/collection/{id}'
    );

} // constructor patterns

// postQuery Patterns
{
    // Resource & collection property
    $propertyResources = array();
    $propertyResourceModel = \Loris\Discovery::find(
        $resources[0]->propertyResource->_uri
    );
    foreach ($resources as $resource) {
        if ($resource->propertyResource instanceof $propertyResourceModel->class) {
            $propertyResources[] = $resource->propertyResource;
        }
    }
    if (!empty($propertyResources)) {
        call_user_func(
            array($propertyResourceModel->class, 'query'),
            $propertyResources
        );
    }

    // Object property - resource & collection property
    $propertyResources = array();
    $propertyResourceModel = \Loris\Discovery::find(
        $resources[0]->propertyObject->propertyResource->_uri
    );
    foreach ($resources as $resource) {
        if ($resource->propertyObject->propertyResource instanceof $propertyResourceModel->class) {
            $propertyResources[] = $resource->propertyObject->propertyResource;
        }
    }
    if (!empty($propertyResources)) {
        call_user_func(
            array($propertyResourceModel->class, 'query'),
            $propertyResources
        );
    }

    // Array property - object - resource & collection property
    $propertyResources = array();
    $propertyResourceModel = \Loris\Discovery::find(
        $resources[0]->propertyArrayPattern->propertyResource->_uri
    );
    foreach ($resources as $resource) {
        foreach ($resource->propertyArray as $propertyArray) {
            if ($propertyArray->propertyResource instanceof $propertyResourceModel->class) {
                $propertyResources[] = $propertyArray->propertyResource;
            }
        }
    }
    if (!empty($propertyResources)) {
        call_user_func(
            array($propertyResourceModel->class, 'query'),
            $propertyResources
        );
    }


    // Array - resource & collection items
    $propertyArrayResources = array();
    $propertyArrayResourceModel = \Loris\Discovery::find(
        $resources[0]->propertyArrayPattern->_uri
    );
    foreach ($resources as $resource) {
        foreach ($resources->propertyArray as $propertyArrayResource) {
            if ($propertyArrayResource instanceof $propertyArrayResourceModel->class) {
                $propertyArrayResources[] = $propertyArrayResource;
            }
        }
    }
    if (!empty($propertyArrayResources)) {
        call_user_func(
            array($propertyArrayResourceModel->class, 'query'),
            $propertyArrayResources
        );
    }

} // postQuery patterns

// fromResults Patterns
{
    // Primitive property (string)
    $this->stringProperty = $results->stringProperty;

    // Primitive property (number)
    $this->numberProperty = intval(
        $results->numberProperty
    );

    // Primitive property (boolean)
    $this->booleanProperty = ($results->booleanProperty == 1);

    // Resource property
    if ($results->resourcePropertyId !== null) {
        $this->resourceProperty->id(
            $results->resourcePropertyId
        );
    } else {
        $this->resourceProperty = new NullResource();
    }

    // Collection property
    if ($results->collectionPropertyId !== null) {
        $this->collectionProperty->id(
            $results->collectionPropertyId
        );
        $this->collectionProperty->meta->total = intval(
            $results->collectionPropertyTotal
        );
    } else {
        $this->collectionProperty = new NullResource();
    }

    // Object property - primitive property (string)
    $this->objectProperty->stringProperty = $results->objectProperty->stringProperty;

    // Object property - primitive property (number)
    $this->objectProperty->numberProperty = intval(
        $results->objectProperty->numberProperty
    );

    // Object property - primitive property (boolean)
    $this->objectProperty->booleanProperty = ($results->objectProperty->booleanProperty == 1);

    // Object property - resource property
    if ($results->objectProperty->resourcePropertyId !== null) {
        $this->objectProperty->resourceProperty->id(
            $results->objectProperty->resourcePropertyId
        );
    } else {
        $this->objectProperty->resourceProperty = new NullResource();
    }

    // Object property - collection property
    if ($results->objectProperty->collectionPropertyId !== null) {
        $this->objectProperty->collectionProperty->id(
            $results->objectProperty->collectionPropertyId
        );
        $this->objectProperty->collectionProperty->meta->total = intval(
            $results->objectProperty->collectionPropertyTotal
        );
    } else {
        $this->objectProperty->collectionProperty = new NullResource();
    }

    // Array property - primitive strings
    foreach ($results->arrayProperty as $item) {
        $this->arrayProperty[] = $item;
    }

    // Array property - primitive numbers
    foreach ($results->arrayProperty as $item) {
        $this->arrayProperty[] = intval($item);
    }

    // Array property - primitive booleans
    foreach ($results->arrayProperty as $item) {
        $this->arrayProperty[] = ($item == 1);
    }

    // Array property - resource items
    foreach ($results->arrayProperty as $item) {
        if ($item->resourceItemId !== null) {
            $resourceItem = clone $this->arrayPropertyPattern;
            $resourceItem->id(
                $item->resourceItemId
            );
            $this->arrayProperty[] = $resourceItem;
        }
    }

    // Array property - collection items
    foreach ($results->arrayProperty as $item) {
        if ($item->collectionItemId !== null) {
            $collectionItem = clone $this->arrayPropertyPattern;
            $collectionItem->id(
                $item->collectionItemId
            );
            $this->arrayProperty[] = $collectionItem;
        }
    }

    // Array property - Object property
    for ($i = 0; $i < count($results->arrayProperty); $i++) {
        $this->arrayProperty[] = clone $this->arrayPropertyPattern;
    }

    // Array property - Object property - primitive property (string)
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->stringProperty = $results->arrayProperty[$i]->stringProperty;
    }

    // Array property - Object property - primitive property (number)
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->numberProperty = intval(
            $results->arrayProperty[$i]->numberProperty
        );
    }

    // Array property - Object property - primitive property (boolean)
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->booleanProperty = ($results->arrayProperty[$i]->booleanProperty == 1);
    }

    // Array property - Object property - resource property
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        if ($results->arrayProperty[$i]->resourcePropertyId !== null) {
            $this->arrayProperty[$i]->resourceProperty->id(
                $results->arrayProperty[$i]->resourcePropertyId
            );
        } else {
            $this->arrayProperty[$i]->resourceProperty = new NullResource();
        }
    }

    // Array property - Object property - collection property
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        if ($results->arrayProperty[$i]->collectionPropertyId !== null) {
            $this->arrayProperty[$i]->collectionProperty->id(
                $results->arrayProperty[$i]->collectionPropertyId
            );
            $this->arrayProperty[$i]->collectionProperty->meta->total = intval(
                $results->arrayProperty[$i]->collectionPropertyTotal
            );
        } else {
            $this->arrayProperty[$i]->collectionProperty = new NullResource();
        }
    }

} // fromResults Patterns

// doExpansions Patterns
{
    // Resource & collection property
    if (array_key_exists('resourceProperty', $this->expansions)) {

        $resourcePropertyModel = \Loris\Discovery::find(
            $this->resourceProperty->_uri
        );
        $this->resourceProperty = new $resourcePropertyModel->class(
            $this->resourceProperty->id()
        );

        if (is_array($this->expansions['resourceProperty'])) {
            $this->resourceProperty->expand($this->expansions['resourceProperty']);
        }
    }

    // Array property - object property - resource & collection property
    if (array_key_exists('arrayProperty', $this->expansions) &&
        array_key_exists('resourceProperty', $this->expansions['arrayProperty']) &&
        count($this->arrayProperty) > 0) {

        $resourcePropertyModel = \Loris\Discovery::find(
            $this->arrayProperty[0]->resourceProperty->_uri
        );
        $expandResourceProperty = is_array($this->expansions['arrayProperty']['resourceProperty']);
        for ($i = 0; $i < count($this->arrayProperty); $i++) {
            $this->arrayProperty[$i]->  = new $resourcePropertyModel->class(
                $this->arrayProperty[$i]->resourceProperty->id()
            );

            if ($expandResourceProperty) {
                $this->arrayProperty[$i]->resourceProperty->expand(
                    $this->expansions['arrayProperty']['resourceProperty']
                );
            }
        }
    }

    // Array property - resource or collection items
    if (array_key_exists('arrayProperty', $this->expansions) &&
        count($this->arrayProperty) > 0) {

        $arrayPropertyModel = \Loris\Discovery::find(
            $this->arrayProperty[0]->_uri
        );
        $expandArrayProperty = is_array($this->expansions['arrayProperty']);
        for ($i = 0; $i < count($this->arrayProperty); $i++) {
            $this->arrayProperty[$i] = new $arrayPropertyModel->class(
                $this->arrayProperty[$i]->id()
            );

            if ($expandArrayProperty) {
                $this->arrayProperty[$i]->expand(
                    $this->expansions['arrayProperty']
                );
            }
        }
    }

} // doExpansions Patterns

// serialize Patterns
{

    // primitive property (string)
    $serialized->stringProperty = $this->stringProperty;

    // primitive property (boolean)
    $serialized->booleanProperty = $this->booleanProperty;

    // primitive property (number)
    $serialized->numberProperty = $this->numberProperty;

    // Resource property
    $serialized->resourceProperty = $this->resourceProperty->serialize();

    // Collection property
    $serialized->collectionProperty = $this->collectionProperty->serialize();

    // Object property
    $serialized->objectProperty = new \stdClass;

    // Object property - primitive property (string)
    $serialized->objectProperty->stringProperty = $this->objectProperty->stringProperty;

    // Object property - primitive property (number)
    $serialized->objectProperty->numberProperty = $this->objectProperty->numberProperty;

    // Object property - primitive property (boolean)
    $serialized->objectProperty->booleanProperty = $this->objectProperty->booleanProperty;

    // Object property - resource property
    $serialized->objectProperty->resourceProperty = $this->objectProperty->resourceProperty->serialize();

    // Object property - collection property
    $serialized->objectProperty->collectionProperty = $this->objectProperty->collectionProperty->serialize();

    // Array property
    $serialized->arrayProperty = array();

    // Array property - primitive strings
    $serialized->arrayProperty = clone $this->arrayProperty;

    // Array property - primitive numbers
    $serialized->arrayProperty = clone $this->arrayProperty;

    // Array property - primitive booleans
    $serialized->arrayProperty = clone $this->arrayProperty;

    // Array property - resources
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $serialized->arrayProperty[] = $this->arrayProperty[$i]->serialize();
    }

    // Array property - collections
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $serialized->arrayProperty[] = $this->arrayProperty[$i]->serialize();
    }

    // Array property - Object property
    for ($i = 0; $i < count($results->arrayProperty); $i++) {
        $this->arrayProperty[] = new \stdClass;
    }

    // Array property - Object property - primitive property (string)
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->stringProperty = $results->arrayProperty[$i]->stringProperty;
    }

    // Array property - Object property - primitive property (number)
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->numberProperty = $results->arrayProperty[$i]->numberProperty;
    }

    // Array property - Object property - primitive property (boolean)
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->booleanProperty = $results->arrayProperty[$i]->booleanProperty;
    }

    // Array property - Object property - resource property
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->resourceProperty = $results->arrayProperty[$i]->resourceProperty->serialize();
    }

    // Array property - Object property - collection property
    for ($i = 0; $i < count($this->arrayProperty); $i++) {
        $this->arrayProperty[$i]->collectionProperty = $results->arrayProperty[$i]->collectionProperty->serialize();
    }

} // serialized Patterns



