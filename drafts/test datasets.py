
# Test Datasets

PERSON_COLLECTION = [
    [ # Rowset 1
        {
            limit: 20,
            offset: 0,
            total: 2
        }
    ],
    [ # Rowset 2
        {id: '1'}, {id: '2'}
    ]
]

PERSON_1 = [
    [ # Rowset 1
        id: '1',
        firstName: 'Chase',
        lastName: 'McManning',
        departmentId: '40180',
        certificatesId: '1',
        friendsId: '1',
        friendsTotal: 1
    ],
    [ # Rowset 2
        {
            date: '2015-07-19',
            message: 'Test activity'
        },
        {
            date: '2015-07-20',
            message: 'Monday activity'
        }
    ]
]

PERSON_2 = [
    [ # Rowset 1
        id: '2',
        firstName: 'John',
        lastName: 'Ray',
        departmentId: '40180',
        certificatesId: '2',
        friendsId: '2',
        friendsTotal: 0
    ],
    [ # Rowset 2
        # no activities
    ]
]

PERSON_1_CERTIFICATES = [
    [ # Rowset 1 (Attributes)
        # Nothing
    ],
    [ # Rowset 2 (CITI)
        status: 'Complete',
        completionDate: '2015-03-01'
    ],
    [ # Rowset 3 (COI)
        status: 'Complete',
        completionDate: '2015-06-01'
    ],
    [ # Rowset 4 (COI.companies)
        'The Ohio State University',
        'McDonalds'
    ]
]

PERSON_2_CERTIFICATES = [
    [ # Rowset 1 (Attributes)
        # Nothing
    ],
    [ # Rowset 2 (CITI)
        status: 'Incomplete',
        completionDate: None
    ],
    [ # Rowset 3 (COI)
        status: 'Complete',
        completionDate: '2015-06-06'
    ],
    [ # Rowset 4 (COI.companies)
        # Nothing
    ]
]
PERSON_1_FRIENDS_COLLECTION = [
    [ # Rowset 1
        {
            limit: 20,
            offset: 0,
            total: 1
        }
    ],
    [ # Rowset 2
        {id: '2'}
    ]
]

PERSON_2_FRIENDS_COLLECTION = [
    [ # Rowset 1
        {
            limit: 20,
            offset: 0,
            total: 1
        }
    ],
    [ # Rowset 2
        {id: '1'}
    ]
]

DEPARTMENT_40180 = [
    [
        {
            id: '40180',
            title: 'ORIS',
            directorId: '2'
        }
    ]
]

