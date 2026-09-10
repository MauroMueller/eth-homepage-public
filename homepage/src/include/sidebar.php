<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/sidebar.css',
]);
App::asset_registry()->add_js([
    App::config()->base_url().'/assets/js/sidebar.js',
]);

/* Load used phps */
$render_menu_toggle = $render_menu_toggle ?? include($SRC_DIR.
                        '/include/small-components/menu-toggle.php');
$render_theme_toggle = $render_theme_toggle ?? include($SRC_DIR.
                        '/include/small-components/theme-toggle.php');

function display_subtree($node) { ?>
    <div class="sidebar-element">
        <div class="sidebar-element-head">
            <?php
                if (!is_null($node->url)) {
                    ?><a class="sidebar-element-content" href="<?php
                        echo htmlspecialchars($node->url);
                    ?>"><?php
                } else {
                    ?><span class="sidebar-element-content"><?php
                }

                if (!is_null($node->icon)) {
                    ?><i class="fa-regular <?php
                        echo htmlspecialchars($node->icon);
                    ?>"></i><?php
                }

                if (!is_null($node->icon) && !is_null($node->text)) {
                    ?><span> </span><?php
                }

                if (!is_null($node->text)) {
                    ?><span><?php
                        echo htmlspecialchars($node->text);
                    ?></span><?php
                }

                if (!is_null($node->url)) {
                    ?></a><?php
                } else {
        			?></span><?php
                }
            ?>
        </div>
        <div class="sidebar-element-children">
            <?php
                foreach ($node->children as $child)
                    display_subtree($child);
            ?>
        </div>
    </div>
<?php }

return function() use ($page, $render_menu_toggle, $render_theme_toggle) { ?>
    <div class="sidebar hidden">
        <div class="sidebar-inner">
            <div class="top">
                <?php $render_menu_toggle(); ?>
                <?php $render_theme_toggle(); ?>
            </div>
            <nav class="sidebar-nav">
                <?php
                    $trees = App::sidebar()->from($page['current_page']);
                    foreach ($trees as $tree)
                        display_subtree($tree);
                ?>
            </nav>
        </div>
    </div>
<?php } ?>
