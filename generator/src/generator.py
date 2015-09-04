import os
import re

import glob
import json
import yaml

# Template management for rendering PHP files
from jinja2 import Environment, FileSystemLoader # easy_install Jinja2

def camelcase(s):
    #s = s.replace('_', ' ')
    #r = ''.join(w for w in s.title() if not w.isspace())
    if type(s) == list:
        return [x[0].lower() + x[1:] for x in s]
    else:
        return s[0].lower() + s[1:]

def pascalcase(s):

    if type(s) == list:
        return [x[0].upper() + x[1:] for x in s]
    else:
        return s[0].upper() + s[1:]

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
    j2_env.filters['pascalcase'] = pascalcase

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
    j2_env.globals['id_keys'] = resource['ids'] if 'ids' in resource else []

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


def generate_resources(root_path, spec):

    # Compile the Jinja template. This only needs to be done once. 
    j2_env = Environment(
        loader=FileSystemLoader(os.path.dirname(os.path.abspath(__file__))),
        trim_blocks=True,
        lstrip_blocks=True
    )

    # Add some custom filters
    j2_env.filters['camelcase'] = camelcase
    j2_env.filters['pascalcase'] = pascalcase

    # Apply some globals to the environment. Note that this
    # is done to access common properties from within macros,
    # where normally we wouldn't be able to because of call scope.

    # Note that JSON date(time) formats are ISO 8601
    j2_env.globals['json_date_format'] = 'Y-m-d'
    j2_env.globals['json_datetime_format'] = 'Y-m-dTH:i:sZ'
    j2_env.globals['input_date_format'] = 'Y-m-d'
    j2_env.globals['input_datetime_format'] = 'Y-m-d H:i:s'

    # Load resources into fragment files
    base_resource = j2_env.get_template('templates/base_resource.jinja')
    impl_resource = j2_env.get_template('templates/resource.jinja')
    base_collection = j2_env.get_template('templates/base_collection.jinja')
    impl_collection = j2_env.get_template('templates/collection.jinja')

    resolve_refs(spec)

    for name, attributes in spec['definitions'].items():
        if attributes['type'] == 'resource':
            base = base_resource
            impl = impl_resource
        elif attributes['type'] == 'collection':
            base = base_collection
            impl = impl_collection
        else:
            raise Error('Unknown type [{}] for [{}]'.format(attributes['type'], name))

        # Write the base code source
        with open(root_path + '/Resource/Base/' + name + '.php', 'w') as f:
            f.write(base.render(
                id = name,
                uri = attributes['uri'],
                id_keys = attributes['ids'] if 'ids' in attributes else [],
                id_var = camelcase(name),
                id_var_plural = camelcase(name) + 's',
                properties=attributes['properties']
            ))

        # Write the implementation source, if it doesn't already exist
        if not os.path.isfile(root_path + '/Resource/' + name + '.php'):
            with open(root_path + '/Resource/' + name + '.php', 'w') as f:
                f.write(impl.render(
                    id = name,
                    uri = attributes['uri'],
                    id_keys = attributes['ids'] if 'ids' in attributes else [],
                    id_var = camelcase(name),
                    id_var_plural = camelcase(name) + 's',
                    properties=attributes['properties']
                ))


def find_resource(spec, name):
    if name not in spec['definitions']:
        raise 'Resource [{}] not in spec'.format(name)

    return spec['definitions'][name]


def resolve_ref(spec, ref):
    """
        Resolve a single $ref value to a resource summary
        (containing resource id, type, uri, and ids)
    """
    resource = find_resource(spec, ref[14:])
    return dict(
        resource=ref[14:],
        type=resource['type'],
        uri=resource['uri'],
        ids=resource['ids'] if 'ids' in resource else []
    )

def resolve_refs(spec):
    """
        Walk the spec tree and replace any $ref with a minimal summary
        of the referenced resource. 

        Specifically:
            $ref: "/resource"
        becomes:
            type: "resource",
            resource: "ObjectResource",
            uri: "/object-resource/{id}",
            ids: [
                "id"
            ]
        or (for collections):
            type: "collection",
            collection: "ObjectCollection",
            uri: "/object-collection/{id}",
            ids: [
                "id"
            ]
    """
    for id, attributes in spec['definitions'].items():
        print(id)
        for prop, details in attributes['properties'].items():
            if '$ref' in details:
                ref = details['$ref']
                del details['$ref']
                resolved = resolve_ref(spec, ref)
                details.update(resolved)
            else:
                if details['type'] == 'array':
                    if '$ref' in details['items']:
                        ref = details['items']['$ref']
                        del details['items']['$ref']
                        resolved = resolve_ref(spec, ref)
                        details['items'].update(resolved)
                    elif details['items']['type'] == 'object':
                        # array of objects
                        for prop2, details2 in details['items']['properties'].items():
                            if '$ref' in details2:
                                ref = details2['$ref']
                                del details2['$ref']
                                resolved = resolve_ref(spec, ref)
                                details2.update(resolved)
                elif details['type'] == 'object':
                    for prop2, details2 in details['properties'].items():
                        if '$ref' in details2:
                            ref = details2['$ref']
                            del details2['$ref']
                            resolved = resolve_ref(spec, ref)
                            details2.update(resolved)

    print(json.dumps(spec, indent=4))

def compile_spec(root_path):
    spec = """
info:
    version: 1.0
    title: Test Spec
"""

    # Include definition files
    spec += '\ndefinitions:\n'
    for path in glob.glob(root_path + '/definitions/*.yaml'):
        with open(path, 'r') as f:
            spec += '\n  # {}\n'.format(path)
            spec += f.read()

    yaml_spec = yaml.load(spec)

    outfile = root_path + '/spec-{}'.format(
        yaml_spec['info']['version']
    )

    # Write out the compiled YAML spec
    with open(outfile + '.yaml', 'w') as f:
        print('Writing {} spec to {}.yaml...'.format(
            yaml_spec['info']['title'], 
            outfile
        ))
        f.write(spec)

    # Cross-compile the spec to JSON as well
    with open(outfile + '.json', 'w') as f:
        print('Writing {} spec to {}.json...'.format(
            yaml_spec['info']['title'], 
            outfile
        ))
        f.write(json.dumps(yaml_spec, indent=4))

    return yaml_spec


if __name__ == '__main__':
    spec = compile_spec('schema')
    generate_resources('loris', spec)

    osu_spec = compile_spec('osu/schema')
    generate_resources('osu/loris', osu_spec)
    
    #generate_resource_php(TEST)