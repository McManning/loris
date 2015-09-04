<?php
namespace Loris\Resource\Base;

/**
 * MetaCollection
 */
class MetaCollection extends Meta
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 10;

    function __construct($id, $uri)
    {
        parent::__construct($id, $uri);

        $this->meta->type = 'collection';
        $this->meta->page = self::DEFAULT_PAGE;
        $this->meta->limit = self::DEFAULT_LIMIT;
        $this->meta->total = null;
        $this->meta->sort = new \stdClass;
        $this->meta->sort->property = null;
        $this->meta->sort->order = null; // ASC or DESC
    }

    /**
     * Get or set the current page of the collection.
     * When called with a parameter, this sets the current
     * page of the collection and returns $this. Note that this will 
     * only have an effect if performed before query(). If no 
     * parameter is specified, this will return the current page.
     *
     * @param integer $page to set or null to retrieve the page.
     *
     * @return integer|this
     */
    public function page($page = null) 
    {
        if ($page === null) {
            return $this->meta->page;
        }

        $this->meta->page = $page;
        return $this;
    }

    /**
     * Get or set the current page limit of the collection.
     * When called with a parameter, this sets the current
     * limit of the collection and returns $this. Note that this will 
     * only have an effect if performed before query(). If no 
     * parameter is specified, this will return the current limit.
     *
     * @param integer $limit to set or null to retrieve the limit.
     *
     * @return integer|this
     */
    public function limit($limit = null) 
    {
        if ($limit === null) {
            return $this->meta->limit;
        }

        $this->meta->limit = $limit;
        return $this;
    }

    /**
     * Get or set the current page limit of the collection.
     * When called with a parameter, this sets the current
     * limit of the collection and returns $this. Note that this will 
     * only have an effect if performed before query(). If no 
     * parameter is specified, this will return the current limit.
     *
     * @param string $property to set or null to retrieve the sort.
     * @param string $order to set, defaults to ASC. (ASC or DESC)
     *
     * @return \stdClass|this
     */
    public function sort($property = null, $order = 'ASC') 
    {
        if ($property === null) {
            return $this->meta->sort;
        }

        // Verify order. Technically we should verify property too,
        // but that would require magic to test for resource properties.
        if (!in_array($order, array('ASC', 'DESC'))) {
            throw new \Exception('Sort order must be either ASC or DESC');
        }

        $this->meta->sort->property = $property;
        $this->meta->sort->order = $order;
        return $this;
    }
}
