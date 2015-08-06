<?php
namespace Loris\Resource\Base;

/**
 * Meta
 */
class Meta
{
    public $_id = null;
    public $_uri = null;

    public $meta = null;

    function __construct($id, $uri)
    {
        $this->meta = new \stdClass();
        $this->meta->type = 'resource';
        $this->meta->uri = null;

        // If there's no id specified, generate a uid.
        // This is used for resource and collections that
        // do not have a referential ID for queries, but
        // need to have an ID for queries involving sets
        // of results. 
        if ($id === null) {
            $id = uniqid('uid:');
        }
        
        $this->id($id);
        $this->uri($uri);
    }

    public function id($id = null) 
    {
        if ($id) {
            $this->_id = $id;
            $this->updateMetaUri();
        }

        return $this->_id;
    }

    public function uri($uri = null) 
    {
        if ($uri) {
            $this->_uri = $uri;
            $this->updateMetaUri();
        }

        return $this->_uri;
    }

    protected function updateMetaUri() 
    {
        if (strpos($this->_uri, '{id}') !== false) {
            $this->meta->uri = str_replace(
                '{id}',
                $this->_id,
                $this->_uri
            );
        } else {
            $this->meta->uri = $this->_uri;
        }
    }

    /**
     * @return stdClass
     */
    public function serialize()
    {
        $serialized = new \stdClass();
        $serialized->id = $this->_id;
        $serialized->meta = $this->meta;
        
        return $serialized;
    }
}
