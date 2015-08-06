<?php

class CompareToSchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return mixed schema
     */
    public function testPrimitiveStringSuccess()
    {
        $json = <<<JSON
        {
            "type": "string"
        }
JSON;
        $schema = json_decode($json);
        $object = 'foo';

        $result = compareToSchema($schema, $object);

        $this->assertEquals(true, $result);

        return $schema;
    }

    /**
     * @depends testPrimitiveStringSuccess
     */
    public function testPrimitiveStringError($schema)
    {
        $object = 6; // error line

        $result = compareToSchema($schema, $object);

        // TODO: Compare error response string
        $this->assertInternalType('string', $result);
    }

    /**
     * @return mixed schema
     */
    public function testObjectSuccess()
    {
        $json = <<<JSON
        {
          "type": "object",
          "properties": {
            "someNumber": {
              "type": "number"
            },
            "someString": {
              "type": "string"
            },
            "someBool": {
              "type": "boolean"
            },
            "someDate": {
              "type": "string",
              "format": "date"
            },
            "someArray": {
              "type": "array",
              "items": {
                "type": "string"
              }
            },
            "someObject": {
              "type": "object",
              "properties": {
                "someBool": {
                  "type": "boolean"
                }
              }
            }
          }
        }
JSON;
        $schema = json_decode($json);
        $object = new \stdClass;
        $object->someNumber = 5;
        $object->someString = "foo";
        $object->someBool = true;
        $object->someDate = '2015-08-05T10:49:00Z'
        $object->someArray = array("foo", "bar");
        $object->someObject = new \stdClass;
        $object->someObject->someBool = true;

        $result = compareToSchema($schema, $object);

        $this->assertEquals(true, $result);

        return $schema;
    }

    /**
     * @depends testObjectSuccess
     */
    public function testObjectError($schema)
    {
        $object = new \stdClass;
        $object->someNumber = 5;
        $object->someString = "foo";
        $object->someBool = true;
        $object->someDate = '2015-08-05T10:49:00Z'
        $object->someArray = array("foo", "bar");
        $object->someObject = new \stdClass;
        $object->someObject->someBool = 5; // error line

        $result = compareToSchema($schema, $object);

        // TODO: Compare error response string
        $this->assertInternalType('string', $result);
    }

    /**
     * @return mixed schema
     */
    public function testArrayOfObjectsSuccess()
    {
        $json = <<<JSON
        {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "someArray": {
                "type": "array",
                "items": {
                  "type": "string"
                }
              },
              "someString": {
                "type": "string"
              }
            }
          }
        }
JSON;
        $schema = json_decode($json);
        $object = array(
            (object) array(
                'someArray' => array('foo'),
                'someString' => 'bar'
            ),
            (object) array(
                'someArray' => array('a', 'b'),
                'someString' => 'c'
            )
        );

        $result = compareToSchema($schema, $object);

        $this->assertEquals(true, $result);

        return $schema;
    }

    /**
     * @depends testArrayOfObjectsSuccess
     */
    public function testArrayOfObjectsError($schema)
    {
        $object = array(
            'foo', // error line
            12
        )

        $result = compareToSchema($schema, $object);

        // TODO: Compare error response string
        $this->assertInternalType('string', $result);
    }

}
