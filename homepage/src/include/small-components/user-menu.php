<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/small-components/user-menu.css',
]);
App::asset_registry()->add_js([
    App::config()->base_url().'/assets/js/small-components/user-menu.js',
]);

return function() use ($page) { ?>
    <?php $t = App::lang()->component('user-menu'); ?>
    <?php
        $return_exceptions = App::config()->user_menu()['return_exceptions'];
        $return = in_array($page['current_page'], $return_exceptions, true)
                ? urldecode($_GET['return'] ?? safe_relative_url(null))
                : $_SERVER['REQUEST_URI'];
        $return = htmlspecialchars(urlencode($return), ENT_QUOTES, 'UTF-8');
    ?>
    <nav class="topbar-user">
        <div class="topbar-user-button">
            <?php $username = App::auth()->user()?->username() ?? ''; ?>
            <span><?php echo htmlspecialchars($username); ?></span>
            <i class="fa-regular fa-circle-user"></i>
        </div>
        <div class="topbar-user-menu">
            <div class="topbar-user-menu-inner">
                <?php if (is_null(App::auth()->user())): ?>
                <a href="/account/login?return=<?php echo $return; ?>">
                    <?php echo htmlspecialchars($t['login_button']); ?>
                </a>
                <a href="/account/register?return=<?php echo $return; ?>">
                    <?php echo htmlspecialchars($t['register_button']); ?>
                </a>
                <a href="/account/preferences">
                    <?php echo htmlspecialchars($t['preferences_button']); ?>
                </a>
                <?php else: ?>
                <a href="/account">
                    <?php echo htmlspecialchars($t['account_button']); ?>
                </a>
                <a href="/account/preferences">
                    <?php echo htmlspecialchars($t['preferences_button']); ?>
                </a>
                <form method="POST" action="/account/logout">
                    <input type="hidden" name="return"
                        value="<?php echo $return; ?>">
                    <button>
                        <?php echo htmlspecialchars($t['logout_button']); ?>
                    </button>
                </form>
                <?php endif ?>
            </div>
        </div>
    </nav>
<?php } ?>
