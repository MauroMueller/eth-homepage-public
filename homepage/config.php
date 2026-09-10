<?php
$config = [
    'base_url' => '', // path prefix where this page is hosted
                      // e.g. '' for root, '/~username' or '/project'
    'absolute_base_url' => null,

    'database' => [
        'dsn' => null,
        'username' => null,
        'password' => null,
    ],

    'mail' => [
        'send' => false,
        'smtp' => [
            'host' => null,
            'username' => null,
            'password' => null,
            'port' => null,
        ],
        'default_addresses' => [
            'from_address' => null,
            'from_name' => null,
            'set_replyto' => true,
            'replyto_address' => null,
            'replyto_name' => null,
            'set_bcc' => true,
            'bcc_address' => null,
        ],
        'log' => false,
        'log_location' => dirname($BASE_DIR) . '/storage/mail-logs',
    ],

    'pw_hashing' => [
        'peppers' => null,
        'current_pepper_version' => null,
        'pepper_algo' => 'sha256',
        'pw_algo' => PASSWORD_DEFAULT,
        'pw_options' => ['cost' => 12],
        'dummy_hash' => '$2a$12$R9h/cIPz0gi.URNNX3kh2OPST9/PgBkqquzi.Ss7KIUgO2t0jWMUW',
    ],

    'tokens' => [
        'pepper' => null,
        'hash_algo' => 'sha256',
        'types_without_login' => [
            'password_reset',
        ],
    ],

    'supported_preferences' => [
        'language' => [
            'en',
            'de',
        ],
        'timezone' => DateTimeZone::listIdentifiers(),
        'locale' => ['de-CH'],
    ],

    'session_defaults' => [
        'language' => 'en',
        'timezone' => 'Europe/Zurich',
        'locale' => 'de-CH',
    ],

    'user_defaults' => [
        'verified' => (int) false,
        'role' => 'public',
    ],

    'reserved_usernames' => [
        'root', 'admin', 'system', 'mauro', 'maumueller',
    ],

    'roles' => [
        'public' => [
            'permissions' => [
                'view_public',
                'download_public_files',
                'comment_anonymous',
            ],
        ],
        'verified' => [
            'inherits' => ['public'],
        ],
        'eth' => [
            'inherits' => ['verified'],
            'permissions' => [
                'comment',
            ],  
        ],
        'moderator' => [
            'inherits' => ['eth'],
            'permissions' => [
                'delete_comments',
            ],
        ],
        'admin' => [
            'inherits' => ['moderator'],
            'permissions' => [
                'view_private',
                'edit_public',
                'edit_private',
                'upload_files',
                'download_private_files',
                'delete_files',
                'access_testing_site',
            ],
        ],
    ],

    'route_permissions' => [
        'all' => [
            '' => ['view_public'],
            'private/' => ['view_private'],
        ],
        'pages' => [],
        'actions' => [
            'files/upload/' => ['upload_files'],
            'files/delete/' => ['delete_files'],
        ],
    ],

    'testing_mode' => [
        'enabled' => false, // change this to true to enable testing mode where
                            // special permissions are required for the entire
                            // page (with some exceptions).
        'required_permissions' => ['access_testing_site'],
        'exempt_routes' => [
            'all' => ['account/'],
            'pages' => [],
            'actions' => [],
        ],
    ],

    'navigation' => [
        'exceptions' => [
            '' => [
                'all' => [
                    'icon' => 'fa-house',
                ],
                'breadcrumbs' => [
                    'text' => null,
                ],
            ],
            'teaching' => [
                'all' => [
                    'hidden' => true,
                ],
            ],
            'account' => [
                'all' => [
                    'icon' => 'fa-circle-user',
                ],
                'sidebar' => [
                    'hidden' => true,
                    'keep_children' => false,
                ],
            ],
            'private' => [
                'sidebar' => [
                    'hidden' => true,
                    'keep_children' => false,
                ],
            ],
        ],
    ],

    'user_menu' => [
        'return_exceptions' => [
            'account/login',
            'account/register',
            'account/reset-password',
        ],
    ],

    'file_service' => [
        'storage_location' => dirname($BASE_DIR) . '/storage/files',
    ],

    'icons' => [
        'dir' => $BASE_DIR . '/assets/icons',
        'svg_name' => 'icon.svg',
        'ico_name' => 'favicon.ico',
        'ico_sizes' => [16, 32, 48, 256],
        'png_name' => fn ($size) => 'icon-' . $size . '.png',
        'png_sizes' => [180, 192, 512],
    ],
];

$local = dirname($BASE_DIR) . '/config.local.php';
if (file_exists($local))
    $config = array_replace_recursive($config, require($local));

return $config;
?>
