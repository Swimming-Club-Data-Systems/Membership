<?php

/**
 * SCDS Membership Main Menu (JSON)
 */

$menu = [
  'leftMenu' => [
    [
      'type' => 'navbar-item',
      'title' => 'Home',
      'id' => 'home',
      'url' => '',
      'externalUrl' => false,
      'children' => null,
      'classes' => null,
      'visibility' => [
        'type' => 'hide',
        'types' => [],
        'onCurrentAccessLevel' => true
      ]
    ],
    [
      'type' => 'navbar-item',
      'title' => 'Members & Squads',
      'id' => 'members-squads',
      'url' => '#',
      'externalUrl' => false,
      'children' => [
        [
          'type' => 'dropdown-item',
          'title' => 'Member directory',
          'id' => 'directory',
          'url' => 'members',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Add member',
          'id' => 'add',
          'url' => 'members/new',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Squads',
          'id' => 'squads',
          'url' => 'squads',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Squad moves',
          'id' => 'squad-moves',
          'url' => 'squads/moves',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Access keys',
          'id' => 'access-keys',
          'url' => 'members/access-keys',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Membership renewal',
          'id' => 'renewal',
          'url' => 'renewal',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Orphan swimmers',
          'id' => 'orphaned',
          'url' => 'members/orphaned',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Squad reps',
          'id' => 'squad-reps',
          'url' => 'squad-reps',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Log books',
          'id' => 'log-books',
          'url' => 'log-books',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
      ],
      'classes' => null,
    ],
    [
      'type' => 'navbar-item',
      'title' => 'Registers',
      'id' => 'registers',
      'url' => 'attendance',
      'externalUrl' => false,
      'children' => [
        [
          'type' => 'dropdown-item',
          'title' => 'Attendance home',
          'id' => 'home',
          'url' => 'attendance',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Take register',
          'id' => 'register',
          'url' => 'attendance/register',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Manage sessions',
          'id' => 'sessions',
          'url' => 'attendance/sessions',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Manage venues',
          'id' => 'venues',
          'url' => 'attendance/venues',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Attendance history',
          'id' => 'history',
          'url' => 'attendance/history',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
      ],
      'visibility' => [
        'type' => 'show',
        'types' => ['Admin', 'Coach'],
        'onCurrentAccessLevel' => true
      ],
      'classes' => null,
    ],
    [
      'type' => 'navbar-item',
      'title' => 'Users',
      'id' => 'users',
      'url' => 'users',
      'externalUrl' => false,
      'children' => [
        [
          'type' => 'dropdown-item',
          'title' => 'All users',
          'id' => 'home',
          'url' => 'users',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'Assisted account registration',
          'id' => 'assisted-registration',
          'url' => 'assisted-registration',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
        [
          'type' => 'dropdown-item',
          'title' => 'User direct debit mandates',
          'id' => 'mandates',
          'url' => 'payments/user-mandates',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
      ],
      'visibility' => [
        'type' => 'hide',
        'types' => [],
        'onCurrentAccessLevel' => true
      ],
      'classes' => null,
    ],
    [
      'type' => 'navbar-item',
      'title' => 'Pay',
      'id' => 'payments',
      'url' => 'payments',
      'externalUrl' => false,
      'children' => [
        [
          'type' => 'dropdown-item',
          'title' => 'Payments home',
          'id' => 'home',
          'url' => 'payments',
          'externalUrl' => false,
          'classes' => null,
          'visibility' => [
            'type' => 'hide',
            'types' => [],
            'onCurrentAccessLevel' => true
          ]
        ],
      ],
      'visibility' => [
        'type' => 'hide',
        'types' => [],
        'onCurrentAccessLevel' => true
      ],
      'classes' => null,
    ],
  ],
  'rightMenu' => [
    
  ]
];

header("content-type: application/json");
echo json_encode($menu);