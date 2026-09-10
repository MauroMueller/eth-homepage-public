<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/small-components/menu-toggle.css',
]);
App::asset_registry()->add_js([
    App::config()->base_url().'/assets/js/small-components/menu-toggle.js',
]);

return function() { ?>
    <label class="menu-toggle">
        <input type="checkbox" hidden>
        <div class="bars">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </label>
<?php } ?>
