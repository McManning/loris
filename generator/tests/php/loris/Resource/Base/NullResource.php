<?php
namespace Loris\Resource\Base;

class NullResource extends Meta
{
    /**
     * Note that $uri is passed in so that iterating a set of resources
     * will still give us valid $uri values, regardless if we picked a 
     * Meta, an instantiated one, or a Null instance. 
     */
    function __construct($uri)
    {
        parent::__construct(null, $uri);
    }

    /**
     * @return null
     */
    public function serialize()
    {
        // TODO: Maybe expand this a bit and provide the requested URI
        // and something more than null. (e.g. "This is what you requested,
        // but it doesn't exist here") not necessarily a 404, since some
        // relationships expect null (like a person's projects, if they have none)
        // but others would indicate a bad relationship (e.g. invalid department 
        // number in the backend)
        return null; // Nothing!
    }
}
