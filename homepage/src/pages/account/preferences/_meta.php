<?php
return [
    'layout' => [
        'current_page' => 'account/preferences',
        'css_additional' => [
            App::config()->base_url().'/assets/css/pages/account/account-form.css',
        ],
        'js_additional' => [
            App::config()->base_url().'/assets/js/pages/account/preferences-form.js',
        ],
    ],
];
?>
