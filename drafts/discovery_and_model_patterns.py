
"""
    Service Discovery theory:

    A service discovery module in every instance of the API.
    You feed it a URI pattern, and it spits out either a related
    model, or a remote request to access that model from an external
    service. 

    Roadblocks:
        - Knowing collection sizes for external resources. If we don't
            expand/request, we should still track the # of items, but
            we can't do that if it's external without making a request.
            Either it's left as an unknown, or ... ? if it is a collection
            we HAVE to make a request regardless (and just don't ask it for
            actual object hydration?)
"""

class MetaCollection(object):
    def __init__(self, id, uri):
        self.id = id
        self.meta = dict(
            uri = uri,
            total = None,
            limit = None,
            offset = None
        )

    def serialize(self):
        # Reformat uri in case it's still in pattern-form
        self.meta.uri = self.meta.uri.format(
            id = self.id
        )
        return dict(
            id = self.id,
            meta = self.meta
        )

class Meta(object):
    def __init__(self, id, uri):
        self.id = id
        self.meta = dict(
            uri = uri
        )

    def serialize(self):
        # Reformat uri in case it's still in pattern-form
        self.meta.uri = self.meta.uri.format(
            id = self.id
        )
        return dict(
            id = self.id,
            meta = self.meta
        )

class PersonCollection(MetaCollection):
    uri = '/person'

    collection = None

    # Cache of expand() parameter for when we 
    # generate models after query()
    expansions = None

    def __init__(self, id, uri):
        super(self.__class__, self).__init__(id, uri)
        #self.id = id
        #self.meta.uri = uri # self.uri.format(id=id)

    @staticmethod
    def query(self, person_collections):

        # It gets complicated if we try to optimally
        # query for multiple collections simutaneously.
        # To avoid that, we take a query hit and execute
        # each individually. Idealy, the data should be 
        # structured in a way that avoids hydrating 
        # multiple collections at once. 
        for person_collection in person_collections:
            person_collection.query_single()

    def query_single(self):
        """ Query to only hydrate ourself. Used by collections
            only to avoid complications of hydrating multiple
            collections simultaneously.
        """
        # some fake query stuff as an example
        # LAZY EXAMPLE: Just return IDs for all sub-objects.
        # More appropriate version is to return the same data
        # as Person.query(), and hydrating the same way. But
        # that's not as generic of a solution, and would be
        # problematic if the resources require multiple rowsets
        # (e.g. `Person.activities`) in which case, we need to
        # also provide more rowsets for that, and offset 
        # accordingly as Rowset 1 is actually for this collection.
        query = """
            // Rowset 1 - Metadata for the collection
            SELECT
                offset = :offset,
                limit = :limit,
                total = (
                    SELECT COUNT(*) 
                    FROM person
                );
    
            // Rowset 2 - IDs in the collection
            SELECT
                id
            FROM
                person
            OFFSET :offset
            LIMIT :limit;
        """
        query.bind(':offset', self.meta.offset)
        query.bind(':limit', self.meta.limit)
        rowsets = query.execute()

        # PROBLEM: We do the count for total twice if this
        # is a sub-resource of something already. Which
        # isn't a horrible thing, but maybe not optimal

        self.from_rowsets(rowsets)

        # Now try to hydrate each Person in our collection 
        # simultaneously. 
        # TODO: Technically this should use Discovery, but 
        # 1. We don't necessarily know the URI to .find() with
        # 2. Do we even want to support external collection items?
        Person.query([p for p in self.collection])

    def from_rowsets(self, rowsets):
        """
        param: (list) rowsets each item is also a list of objects
        """
        attribs_row = rowsets[0][0] # Expect only one row in the first rowset

        # Hydrate attributes
        self.meta.page = attribs_row['page']
        self.meta.limit = attribs_row['limit']
        self.meta.total = attribs_row['total']
        
        # Create a Person for each ID returned in the second rowset
        for row in rows:
            # TODO: Technically this should use Discovery, but 
            # 1. We don't necessarily know the URI to .find() with
            # 2. Do we even want to support external collection items?
            person = Person(
                id = row['id'],
                uri = None # TODO: Where would I get this? Person.uri? 
            )

            # Apply cached expansions for each person
            if self.expansions:
                person.expand(self.expansions)
            
            # Add to our collection
            self.collection.append(person)

    def expand(self, resources):
        # We don't have a collection yet to expand,
        # so cache the expansions until we query()
        self.expansions = resources

    def serialize(self):
        """Serialize this collection into a dict.

        This will also serialize all resources in
        our collection.
        """
        json_dict = dict(
            collection = [c.serialize() for c in self.collection]
        )

        # Merge in the json data created by MetaCollection
        json_dict.update(
            super(self.__class__, self).serialize()
        )

        return json_dict


class ExternalResource(object):
    """Container for a resource that is not of the local process.

    This is for resources that are stored on separate servers for
    load-balancing or data access purposes, allowing support for
    dividing an API into independent microservices. 
    """
    def __init__(self, id, uri):
        # `id` set to match resource class patterns, but unused
        self.id = id 
        self.uri = uri
        self.json = None

    @staticmethod
    def query(self, externalResources):
        # TODO: Figure out how to group queries for external
        # resources, rather than making distinct requests 
        # for each. We'd have to figure out how to determine
        # if a remote resource SUPPORTs a set of IDs to use,
        # which may usually be no.
        for externalResource in externalResources:
            externalResource.query_single()

    def query_single(self):
        self.json = yield cUrl(self.uri)

    def expand(self, resources):
        # Convert the dict down to a flat "a,b,c.d,e" string
        param = "?expand={}".format(TO_GET_PARAMETERS(resources))
        self.uri += param

    def serialize(self):
        # TODO: If the resource doesn't have a `meta` field,
        # then inject one. That'll allow us to integrate with
        # other external resources that aren't using the same API
        # format (e.g. KMData or, god forbid, some CITI API)

        # TODO: This should be a dict, not a JSON string
        return self.json


class Department(Meta):
    """ Example of a simple resource,
        sans commentary to see code size.

    """
    uri = '/department/{id}'

    # Attributes
    title = None # String

    # Relationships
    director = None

    def __init__(self, id, uri):
        super(self.__class__, self).__init__(id, uri)
        
        self.director = Meta(
            uri = '/person/{id}'
        )

    def expand(self, resources):
        if 'director' in resources:
            self.director = Discovery.find(
                self.director.meta.uri
            )

            if type(resources['director']) == dict:
                self.director.expand(
                    resources['director']
                )

    @staticmethod
    def query(self, departments):
        # some fake query stuff as an example
        query = """
            // Rowset 1
            SELECT
                id,
                title,
                directorId
            FROM
                departents
            WHERE
                id IN (:ids);
        """
        query.bind(':ids', ','.join(set([str(d.id) for d in departments])))
        rowsets = query.execute()

        results = REFORMAT(rowsets)

        for id, rowsets in results:
            for department in departments:
                if department.id == id:
                    department.from_rowsets(rowsets)

        directors = [d.director if type(d.director) != Meta for d in departments]
        if len(directors) > 0: 
            person_model = Discovery.find(
                self.director.meta.uri
            )
            person_model.query(directors)

    def from_rowsets(self, rowsets):
        attribs_row = rowsets[0][0]

        self.title = attribs_row['title']
        self.director.id = attribs_row['directorId']

    def serialize(self):
        json_dict = dict(
            title = self.title,
            director = self.director.serialize()
        )

        json_dict.update(
            super(self.__class__, self).serialize()
        )
        return json_dict



class Person(Meta):
    """ Example of a simple resource with attributes 
        and relationships to single resources and 
        collections of resources. 
    """
    uri = '/person/{id}'

    # Attributes
    firstName = None # String
    lastName = None # String
    activities = None # List of objects { date, message }

    # Relationships
    department = None
    certificates = None
    friends = None

    def __init__(self, id, uri):
        super(self.__class__, self).__init__(id, uri)
        #self.id = id
        #self.meta.uri = uri # self.uri.format(id=id)

        # Create meta skeletons for relationships

        # TODO: I don't like defining uris here...
        # they should be defined by their class, except
        # if the class is stored remote, then it doesn't
        # work.
        self.department = Meta(
            uri = '/department/{id}'
        )

        self.certificates = Meta(
            uri = '/person/{id}/certificates'
        )

        self.friends = MetaCollection(
            uri = '/person/{id}/friends'
        )

    def expand(self, resources):
        """
        By the end of this method, every resource should
        either be resolved as a Meta or MetaCollection 
        (for a non-expanded resource), an ExternalResource,
        or an actual containing model for the resource. 
        """
        # Sub-resource relationship
        if 'certificates' in resources:
            # Search for the model for certificates based 
            # on the URI assigned at `__init__`
            self.certificates = Discovery.find(
                self.certificates.meta.uri
            )
            # self.certificates should now either be
            # PersonCertificates or ExternalResource

            if type(resources['certificates']) == dict:
                #Pass in the list of certificate attributes
                self.certificates.expand(
                    resources['certificates']
                )

        # Sub-collection relationship
        if 'friends' in resources:
            # Search for the model based on the URI
            # assigned at `__init__`
            self.friends = Discovery.find(
                self.friends.meta.uri
            )

            if type(resources['friends']) == dict:
                # Pass in the list of attributes to expand
                # for all our friends (Persons)
                self.friends.expand(
                    resources['friends']
                )

    @staticmethod
    def query(self, persons):
        """
        param: (list) persons 
        """
        # Expect a list of Persons to query for
        
        # some fake query stuff as an example
        query = """
            // Rowset 1
            SELECT
                id,
                firstName,
                lastName,
                departmentId, 
                id AS certificatesId, // Same ID as ourselves
                id AS friendsId, // Same ID as ourselves
                friendsTotal = (
                    SELECT COUNT(*) 
                    FROM person_person 
                    WHERE friend_id = id
                )
            FROM
                person
            WHERE
                id IN (:ids);

            // Rowset 2 (activities)
            SELECT
                id,
                date,
                message
            FROM
                activities
            WHERE
                id IN (:ids);
        """
        # Note that set() is used to enforce unique IDs for duplicate
        # persons in the list 
        query.bind(':ids', ','.join(set([str(p.id) for p in persons])))
        rowsets = query.execute()

        # See Rowset Reformat.txt 
        results = REFORMAT(rowsets)
        # Results is now a dict() who's keys are person IDs, and each
        # value is a list of rowsets matching that ID
        for id, rowsets in results:
            for person in persons:
                # For everyone with the same ID, load from rowsets
                if person.id == id:
                    person.from_rowsets(rowsets)

        # Query all expanded Departments
        departments = [p.department if type(p.department) != Meta for p in persons]
        if len(departments) > 0:
            # Use discovery to either query() off of a Department model 
            # or an ExternalResource 
            department_model = Discovery.find(
                self.department.meta.uri
            )
            department_model.query(departments)

        # Query all expanded PersonCertificates
        certificates = [p.certificate if type(p.certificate) != Meta for p in persons]
        if len(certificates) > 0:
            person_certificates_model = Discovery.find(
                self.certificates.meta.uri
            )
            person_certificates_model.query(certificates)

        # Query all expanded PersonFriends
        friends = [p.friends if type(p.friends) != MetaCollection for p in persons]
        if len(friends) > 0:
            person_friends_model = Discovery.find(
                self.friends.meta.uri
            )
            person_friends_model.query(friends)

    def from_rowsets(self, rowsets):
        """
        param: (list) rowsets each item is also a list of objects
        """
        attribs_row = rowsets[0][0] # Expect only one row in the first rowset

        # Hydrate attributes
        self.firstName = attribs_row['firstName']
        self.lastName = attribs_row['lastName']
        
        # Hydrate IDs of relationships
        # Needed for the sub-queries if they were expanded
        # or, if not, for generating the approprate meta URI

        # Resource relationship, hydrate `id` field
        self.department.id = attribs_row['departmentId']
        self.certificates.id = attribs_row['certificatesId']

        # Collection relationship, hydrate `id` and `total` fields
        self.friends.id = attribs_row['friendsId']
        self.friends.meta.total = attribs_row['friendsTotal']

        # Hydrate `activities` from the second rowset
        activity_rows = rowsets[1]
        self.activities = []
        for row in activity_rows.items():
            self.activities.append({
                date = row['date'],
                message = row['message']
            })

    def serialize(self):
        """Serialize this resource into a dict.

        This will also serialize any expanded resources
        or, if not expanded, include their meta fields
        """
        json_dict = dict(
            # Attributes
            firstName = self.firstName,
            lastName = self.lastName,
            activities = self.activities,
            # Relationships
            department = self.department.serialize(),
            certificates = self.certificates.serialize(),
            friends = self.friends.serialize()
        )

        # Merge in the json data created by MetaCollection
        json_dict.update(
            super(self.__class__, self).serialize()
        )

        return json_dict


"""
    Making query sets easier...
    Basically, we need to use an intermediate backend table.

    1.  Create set via a backend query that caches a list of 
        IDs of resources and stores it. Query returns a single set ID (SID).
        e.g. NewSID = GENERATE() 
            INSERT INTO TempTable (SID, ModelID) 
            NewSID, SELECT ResID FROM User WHERE ProjectID = ID
    2.  Pass N models and the SID into a hydrator under that model. 
        Hydrator queries, using SID in the WHERE for the ID. 
        e.g. instead of WHERE ResID = :id, it's
        JOIN TempTable t ON ResID = t.ModelID WHERE t.SID = SID
    3.  Hydrator takes results, populates each model with model.from_row(...)

    Honestly, is it too much to ask to FORCE any backend to support either
    an input of one ID or set of IDs? I would say no...
    Therefore, fuck it! Let them eat cake!
"""

class Discovery(object):

    def __init__(self):
        self.handlers = dict();

    def register(self, resource):
        """Register an internal model as handler for a URI pattern"""
        self.handlers[resource.uri] = resource

    def find(self, uri):
            
        # TODO: URI passed in is an actual URI
        # (e.g. /person/5) we need to RE compare
        # them to the uri for each handler
        # (e.g. RE('/person/5', '/person/[id]') == True)

        # Check for a local handler
        if uri in self.handlers:
            model = self.handlers[uri]
        else:
            model = ExternalResource


# Usage of discovery and models

@api.route('/person')
def get_person():
    """Return all known Person resources
    """
    # ... cache... expansions... etc

    # Retrieve either a PersonCollection or an 
    # ExternalResource to hydrate.
    collection_model = Discovery.find(request.uri)

    # Initialise an instance
    collection = collection_model()

    # Apply expansions 
    collection.expand(expansions)

    # Execute the query to our backend
    collection_model.query([collection])

    # ... cache... filter... return.


@api.route('/person/{id}')
def get_person_by_id(id):
    """Return a single Person resource by ID
    """
    
    # Perform some normalization of our parameters so
    # different orderings of `expand` don't count for
    # different uris. Might be smarter to do TRANSLATE()
    # before cache checking, so we can construct from
    # the translated dict.
    request.uri = NORMALIZE(request.uri)

    # Extract and translate expansions from the URI
    expansions = TRANSLATE(request.param['expand'])

    # Retrieve the base resource uri, without parameters
    base_uri = GET_BASE_URI(request.uri)

    # Generate a hash of our unique URI to check the cache
    cache_hash = CREATE_CACHE_HASH(base_uri, expansions)

    # If it's cached, use that directly 
    if CACHED(cache_hash):
        json_dict = GET_CACHE(cache_hash)
    else:
        # It's not cached, so we need to hit the backend.

        # Find a model to initialise for our resource. This can 
        # either be a Person or an ExternalResource, depending on
        # whether it's an internal or external resource
        person_model = Discovery.find(request.uri)

        # Initialize a new instance
        person = person_model(
            id=id, 
            uri=base_uri
        )

        # Expand sub-resources into their appropriate models,
        # or requests for external resources
        person.expand(expansions)

        # Finally hydrate our person, and fire off any async
        # HTTP requests for hydrating external data.
        person_model.query([person])

        json_dict = person.serialize()
        SET_CACHE(cache_hash, json_dict)

    # Perform ACL filtering on our resulting data and return
    ACL_FILTER(json_dict, request.acls)
    return jsonify(json_dict)


def setup():
    Discovery.register(Person)
    Discovery.register(PersonCollection)



