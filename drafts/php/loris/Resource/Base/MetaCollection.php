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
    }
}
