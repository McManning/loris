<?php
namespace Loris\Resource\Base;

class NullResource extends Meta
{
    function __construct()
    {
        parent::__construct(null, null);
    }

    /**
     * @return null
     */
    public function serialize()
    {
        return null; // Nothing!
    }
}
