<?php
namespace Loris\Resource\Base;

/**
 * Meta
 */
class Meta
{
    public $_uri = null;

    public $meta = null;

    protected $_ids = null;

    function __construct($ids, $uri)
    {
        $this->meta = new \stdClass();
        $this->meta->type = 'resource';
        $this->meta->uri = null;

        $this->_ids = array();

        // Map IDs onto this resource
        if ($ids !== null) {
            foreach ($ids as $p => $v) {
                $this->_ids[] = $p;
                $this->{$p} = $v;
            }
        }
        
        $this->uri($uri);
    }

    function __clone()
    {
        // Prevent references to objects for cloned instances
        // This affects templates stored in resources, when a 
        // template is copied per-instance of a resource.

        // We use a cast chaining hack to deep clone a plain stdClass object.
        // Note that this has to be deep, as MetaCollection may define meta->sort 
        // as another layer of stdClass. 
        $this->meta = (object)(array)$this->meta;
    }

    /**
     * Returns an associative array mapping ID attributes
     * to their respective values. 
     *
     * @return array
     */
    public function ids() 
    {
        $ids = array();

        foreach ($this->_ids as $id) {
            $ids[$id] = $this->{$id};
        }

        return $ids;
    }

    /**
     * Set (if $uri != null) or get the current URI pattern
     * used for this resource. A pattern is defined as a URI
     * string that contains {id} values, where `id` is one 
     * of the primary key IDs. In cases of composite ID resources,
     * uri's such as `/path/to/resource/{firstId}/subresource/{secondId}`
     * are allowed. 
     */
    public function uri($uri = null) 
    {
        if ($uri === null) {
            return $this->_uri;
        }
        
        $this->_uri = $uri;
        $this->updateMetaUri();
        return $this;
    }

    public function updateMetaUri() 
    {
        // Replace patterns containing ID attributes
        // with their true values
        $this->meta->uri = $this->_uri;
        foreach ($this->_ids as $id) {
            $this->meta->uri = str_replace(
                '{' . $id . '}',
                $this->{$id},
                $this->meta->uri
            );
        }
    }

    /**
     * @return stdClass
     */
    public function serialize()
    {
        // Update once again, in case the ids have changed between
        // construction and serialization (Which is often)
        $this->updateMetaUri();

        $serialized = new \stdClass();
        $serialized->meta = $this->meta;
        
        return $serialized;
    }
}
