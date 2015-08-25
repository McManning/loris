<?php
namespace Loris\Resource\Base;

/**
 * MetaCollection
 */
class MetaCollection extends Meta
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 10;

    function __construct($id, $uri, $page = self::DEFAULT_PAGE, $limit = self::DEFAULT_LIMIT)
    {
        parent::__construct($id, $uri);

        $this->meta->type = 'collection';
        $this->meta->page = $page;
        $this->meta->limit = $limit;
        $this->meta->total = null;
    }
}
