<?php
namespace Loris;

class Discovery
{
    private static $uris = array();
    private static $classes = array();

    function __construct()
    {
        // Is singleton
    }

    /**
     * Locate a resource representation by URI. 
     * @todo error handling
     */
    public static function find($uriOrClass)
    {
        // If they passed in a class name
        if (array_key_exists($uriOrClass, self::$uris)) {
            $result = new \stdClass();
            $result->uri = self::$uris[$uriOrClass];
            $result->class = $uriOrClass;
            return $result;
        }

        // If they passed in a URI
        if (array_key_exists($uriOrClass, self::$classes)) {
            $result = new \stdClass();
            $result->uri = $uriOrClass;
            $result->class = self::$classes[$uriOrClass];
            return $result;
        }

        // Not discoverable. 
        throw new \Exception(
            'Unknown URI or ClassName [' . $uriOrClass . ']'
        );
    }

    /**
     * Register a resource class to handle a URI pattern.
     *
     * @param string $uri
     * @param string $className
     */
    public static function register($uri, $className)
    {
        self::$uris[$className] = $uri;
        self::$classes[$uri] = $className;
    }

    public static function registerMany(array $many)
    {
        self::$registered = array_merge(
            self::$registered, 
            $many
        );
    }
}

// Some internal registers
// (this should happen outside this class...)

/*
Discovery::register(Department::URI, '\\Loris\\Department');
Discovery::register(Person::URI, '\\Loris\\Person');
Discovery::register(PersonCollection::URI, '\\Loris\\PersonCollection');
Discovery::register(PersonCoworkers::URI, '\\Loris\\PersonCoworkers');
*/

/*
    Let's talk routing. I'm seeing repetitions between
    doing resource registration and generating an API.
    Shouldn't handlers be built into resources? 


    ... let's go with NO for now. I feel like if that step
    needs to be approached, that's a class we extend off of
    for individual resources. This'll then allow different
    API handlers to be patched in (although the pattern is 
    explicitely for Slim)
*/





