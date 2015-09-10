<?php
namespace Loris;

use FastRoute\RouteCollector;
use FastRoute\RouteParser;
use FastRoute\RouteParser\Std as StdParser;
use FastRoute\DataGenerator;
use FastRoute\DataGenerator\GroupCountBased as GroupCountBasedGenerator;
use FastRoute\Dispatcher\GroupCountBased as GroupCountBasedDispatcher;

class Discovery extends RouteCollector
{
    /**
     * Parser
     *
     * @var \FastRoute\RouteParser
     */
    private $routeParser;

    function __construct()
    {
        $parser = new StdParser;
        $generator = new GroupCountBasedGenerator;
        parent::__construct($parser, $generator);
        $this->routeParser = $parser;
    }

    /**
     * Locate a resource representation by URI. 
     * @todo error handling
     */
    public function find($uri)
    {
        $dispatcher = new GroupCountBasedDispatcher($this->getData());
        return $dispatcher->dispatch('GET', $uri);
    }

    /**
     * Register a resource class to handle a URI pattern.
     *
     * @param string $uri
     * @param string $className
     */
    public function register($pattern, $className)
    {
        $this->addRoute('GET', $pattern, $className);
    }

    public static function registerMany(array $many)
    {

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





