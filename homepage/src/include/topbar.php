<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/topbar.css',
]);
App::asset_registry()->add_js([
    App::config()->base_url().'/assets/js/topbar.js',
]);

/* Load used phps */
$component_dir = $SRC_DIR.'/include/small-components';
$render_menu_toggle = $render_menu_toggle
                        ?? include($component_dir.'/menu-toggle.php');
$render_theme_toggle = $render_theme_toggle
                        ?? include($component_dir.'/theme-toggle.php');
$render_breadcrumbs = $render_breadcrumbs
                        ?? include($component_dir.'/breadcrumbs.php');
$render_user_menu = $render_user_menu
                        ?? include($component_dir.'/user-menu.php');

return function() use ($page, $render_menu_toggle, $render_theme_toggle,
                       $render_breadcrumbs, $render_user_menu) { ?>
    <div class="topbar">
        <div class="topbar-inner">
            <div class="left">
                <?php $render_menu_toggle(); ?>
                <?php $render_theme_toggle(); ?>
            </div>
            <div class="middle">
                <?php $render_breadcrumbs($page['current_page']); ?>
            </div>
            <div class="right">
                <?php $render_user_menu(); ?>
            </div>
        </div>
    </div>
<?php } ?>
