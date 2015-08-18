import os
import re

import json

# Template management for rendering PHP files
from jinja2 import Environment, FileSystemLoader # easy_install Jinja2

def camelcase(s):
    #s = s.replace('_', ' ')
    #r = ''.join(w for w in s.title() if not w.isspace())
    return s[0].lower() + s[1:]

def generate_resource_php(resource):
    """Use Jinja2 to generate base PHP files

    """

    # Compile the Jinja template. This only needs to be done once. 
    j2_env = Environment(
        loader=FileSystemLoader(os.path.dirname(os.path.abspath(__file__))),
        trim_blocks=True,
        lstrip_blocks=True
    )

    # Add some custom filters
    j2_env.filters['camelcase'] = camelcase

    # Apply some globals to the environment. Note that this
    # is done to access common properties from within macros,
    # where normally we wouldn't be able to because of call scope.
    j2_env.globals['id'] = resource['title']
    j2_env.globals['uri'] = resource['uri']
    j2_env.globals['id_var'] = camelcase(resource['title'])
    j2_env.globals['id_var_plural'] = camelcase(resource['title']) + 's'
    j2_env.globals['json_date_format'] = 'Y-m-d'
    j2_env.globals['json_datetime_format'] = 'Y-m-d H:i:s'
    j2_env.globals['input_date_format'] = 'Y-m-d'
    j2_env.globals['input_datetime_format'] = 'Y-m-d H:i:s'
    j2_env.globals['id_keys'] = resource['ids']

    # Load resources into fragment files
    base_template = j2_env.get_template('templates/base_resource.jinja')
    impl_template = j2_env.get_template('templates/resource.jinja')

    with open('BaseGenerated.php', 'w') as f:
        f.write(base_template.render(
            properties=resource['properties']
        ))

    with open('Generated.php', 'w') as f:
        f.write(impl_template.render(
            properties=resource['properties']
        ))


PERSON = { 
    "id": "Person",
    "uri": "/person/{id}",
    "properties": {
      "osuid": {
        "type": "string"
      },
      "firstName": {
        "type": "string"
      },
      "middleName": {
        "type": "string"
      },
      "lastName": {
        "type": "string"
      },
      "username": {
        "type": "string"
      },
      "active": {
        "type": "boolean"
      },
      "jobCode": {
        "type": "string"
      },
      "jobDescription": {
        "type": "string"
      },
      "jobGroup": {
        "type": "string"
      },
      "apptCode": {
        "type": "string"
      },
      "apptDescription": {
        "type": "string"
      },
      "fte": {
        "type": "number"
      },
      "coworkers": {
        "type": "collection",
        "collection": "PersonCoworkers",
        "uri": "/person/{id}/coworkers"
      },
      "department": {
        "type": "resource",
        "resource": "Department",
        "uri": "/department/{id}"
      },
      "otherDepartments": {
        "type": "array",
        "items": {
          "type": "object",
          "properties": {
            "fte": {
              "type": "number"
            },
            "department": {
              "type": "resource",
              "resource": "Department",
              "uri": "/department/{id}"
            }
          }
        }
      },
      "addresses": {
        "type": "array",
        "items": {
          "type": "object",
          "properties": {
            "address1": {
              "type": "string"
            },
            "address2": {
              "type": "string"
            },
            "city": {
              "type": "string"
            },
            "state": {
              "type": "string"
            },
            "zip": {
              "type": "string"
            },
            "room": {
              "type": "string"
            },
            "building": {
              "type": "string"
            },
            "phone": {
              "type": "string"
            }
          }
        }
      }
    }
}

TEST = {
    "title": "GeneratedResource",
    "uri": "/test/{idLeft}/foo/{idRight}", # Contains everything in the "id" attribute list
    "type": "object",
    "ids": [
        "idLeft", # Primary key used for identifying discrete resources
        "idRight"
    ],
    "properties": {
        "idLeft": {
            "type": "string",
            "description": "Composite identifier left"
        },
        "idRight": {
            "type": "string",
            "description": "Composite identifier right"
        },
        "stringProp": {
            "type": "string",
            "description": "A generic string value"
        },
        "boolProp": {
            "type": "boolean",
            "description": "A boolean value"
        },
        "numberProp": {
            "type": "number",
            "description": "A numeric value"
        },
        "dateProp": {
            "type": "string",
            "format": "date",
            "description": "A date value, stored as a PHP DateTime object"
        },
        "objectProp": {
            "type": "object",
            "description": "A complex object containing primitives or references",
            "properties": {
                "opStringProp": {
                    "type": "string"
                },
                "opBoolProp": {
                    "type": "boolean"
                },
                "opNumberProp": {
                    "type": "number"
                },
                "opDateProp": {
                    "type": "string",
                    "format": "date"
                },
                "opResourceProp": {
                    "type": "resource",
                    "resource": "ObjectResource",
                    "uri": "/object-resource/{id}",
                    "ids": [
                        "id"
                    ]
                },
                "opCollectionProp": {
                    "type": "collection",
                    "collection": "ObjectCollection",
                    "uri": "/object-collection/{id}",
                    "ids": [
                        "id"
                    ]
                },
                "opCompositeResourceProp": {
                    "type": "resource",
                    "resource": "ObjectCompositeResource",
                    "uri": "/object-resource/{idLeft}/{idRight}",
                    "ids": [
                        "idLeft",
                        "idRight"
                    ]
                },
                "opCompositeCollectionProp": {
                    "type": "collection",
                    "collection": "ObjectCompositeCollection",
                    "uri": "/object-collection/{idLeft}/{idRight}",
                    "ids": [
                        "idLeft",
                        "idRight"
                    ]
                }
            }
        },
        "resourceProp": {
            "type": "resource",
            "resource": "TestResource",
            "uri": "/resource/{id}",
            "ids": [
                "id"
            ],
            "description": "A reference to another resource"
        },
        "compositeResourceProp": {
            "type": "resource",
            "resource": "TestCompositeResource",
            "uri": "/resource/{idLeft}/and/{idRight}",
            "ids": [
                "idLeft",
                "idRight"
            ],
            "description": "A reference to another resource with a composite ID"
        },
        "collectionProp": {
            "type": "collection",
            "collection": "TestCollection",
            "uri": "/collection/{id}",
            "ids": [
                "id"
            ],
            "description": "A reference to a collection"
        },
        "compositeCollectionProp": {
            "type": "collection",
            "collection": "TestCompositeCollection",
            "uri": "/collection/{idLeft}/{idRight}",
            "ids": [
                "idLeft",
                "idRight"
            ],
            "description": "A reference to a collection with a composite ID"
        },
        "arrayOfStringProp": {
            "type": "array",
            "description": "An unordered list of string values",
            "items": {
                "type": "string"
            }
        },
        "arrayOfNumberProp": {
            "type": "array",
            "description": "An unordered list of numeric values",
            "items": {
                "type": "number"
            }
        },
        "arrayOfDateProp": {
            "type": "array",
            "description": "An unordered list of DateTime objects",
            "items": {
                "type": "string",
                "format": "date"
            }
        },
        "arrayOfResourceProp": {
            "type": "array",
            "description": "An unordered list of references to resources, all of the same type",
            "items": {
                "type": "resource",
                "resource": "ArrayResource",
                "uri": "/array-resource/{id}",
                "ids": [
                    "id"
                ]
            }
        },
        "arrayOfCollectionProp": {
            "type": "array",
            "description": "An unordered list of references to collections, all of the same type",
            "items": {
                "type": "collection",
                "collection": "ArrayCollection",
                "uri": "/array-collection/{id}",
                "ids": [
                    "id"
                ]
            }
        },
        "arrayOfCompositeResourceProp": {
            "type": "array",
            "description": "An unordered list of references to resources, all of the same type",
            "items": {
                "type": "resource",
                "resource": "ArrayCompositeResource",
                "uri": "/array-resource/{idLeft}/{idRight}",
                "ids": [
                    "idLeft",
                    "idRight"
                ]
            }
        },
        "arrayOfCompositeCollectionProp": {
            "type": "array",
            "description": "An unordered list of references to collections, all of the same type",
            "items": {
                "type": "collection",
                "collection": "ArrayCompositeCollection",
                "uri": "/array-collection/{idLeft}/{idRight}",
                "ids": [
                    "idLeft",
                    "idRight"
                ]
            }
        },
        "arrayOfObjectProp": {
            "type": "array",
            "description": "An unordered list of complex objects, each with the same schema containing primitives or references",
            "items": {
                "type": "object",
                "properties": {
                    "aopStringProp": {
                        "type": "string"
                    },
                    "aopBoolProp": {
                        "type": "boolean"
                    },
                    "aopNumberProp": {
                        "type": "number"
                    },
                    "aopDateProp": {
                        "type": "string",
                        "format": "date"
                    },
                    "aopResourceProp": {
                        "type": "resource",
                        "resource": "ObjectResource",
                        "uri": "/object-resource/{id}",
                        "ids": [
                            "id"
                        ]
                    },
                    "aopCollectionProp": {
                        "type": "collection",
                        "collection": "ObjectCollection",
                        "uri": "/object-collection/{id}",
                        "ids": [
                            "id"
                        ]
                    },
                    "aopCompositeResourceProp": {
                        "type": "resource",
                        "resource": "ObjectCompositeResource",
                        "uri": "/object-resource/{idLeft}/{idRight}",
                        "ids": [
                            "idLeft",
                            "idRight"
                        ]
                    },
                    "aopCompositeCollectionProp": {
                        "type": "collection",
                        "collection": "ObjectCompositeCollection",
                        "uri": "/object-collection/{idLeft}/{idRight}",
                        "ids": [
                            "idLeft",
                            "idRight"
                        ]
                    }
                }
            }
        }
    }
}

if __name__ == '__main__':
    generate_resource_php(TEST)
