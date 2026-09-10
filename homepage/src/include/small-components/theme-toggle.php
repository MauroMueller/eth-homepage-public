<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/small-components/theme-toggle.css',
]);
App::asset_registry()->add_js([
    App::config()->base_url().'/assets/js/small-components/theme-toggle.js',
]);

return function() { ?>
    <label class="theme-toggle">
        <input type="checkbox" hidden>
        <span class="sun"></span>
        <div class="rays">
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
            <span></span>
        </div>
    </label>
<?php } ?>
