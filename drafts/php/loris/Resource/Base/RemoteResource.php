<?php
namespace Loris\Resource\Base;

/**
 * A resource that exists on a remote server. 
 * 
 * For resources that are not managed internally by a server
 * instance, but are required by requests (e.g. through expansions)
 * this class encapsulates that resource and performs the 
 * necessary HTTP requests for it's query().
 * 
 * Resources that cannot be properly resolved will internally be
 * represented by error JSON (error, message, and developerMessage)
 * with the appropriate error code (404, 500, etc) in replace of the
 * expected response JSON. This is done to prevent the full request
 * from failing due to a sub-resource not being accessible. 
 */
class RemoteResource extends Meta
{
    private $expansions = null;

    /**
     * @param string $id
     * @param string $uri
     */
    function __construct($id, $uri)
    {
        parent::__construct($id, $uri);
    }

    /**
     * Performs a query for each resource in the list.
     * Note that in the case of a remote resource, these are
     * blocking HTTP network requests for each resource.
     * 
     * @param array(RemoteResource) $remoteResources
     */
    public static function query(array $remoteResources)
    {
        // Check out curl_multi for supporting async(ish...) handling.
        // http://www.onlineaspect.com/2009/01/26/how-to-use-curl_multi-without-blocking/
        // http://php.net/manual/en/function.curl-multi-init.php

        foreach ($remoteResources as $remoteResource) {
            $remoteResource->data = (object)array(
                'error' => 501, // Not Implemented
                'message' => 'Remote Resources not supported yet.',
                'developerMessage' => 'TODO: Implement!'
            );
        }
    }

    /**
     * 
     * @param array $resources
     */
    public function expand(array $resources)
    {

        $this->expansions = $resources;
    }

    public function serialize()
    {
        $serialized = parent::serialize();
        return array_merge($serialized, $this->data);
    }
}
