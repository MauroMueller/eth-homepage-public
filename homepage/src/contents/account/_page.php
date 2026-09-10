<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/pages/account/account-form.css',
]);

return function() use ($page) { ?>
    <?php
        $t = App::lang()->page($page['current_page']);
        $user = App::auth()->user();
        $subtitle = $user?->username() ?? $t['subtitle_no_user'];
    ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>
    <p class="subtitle"><?php echo htmlspecialchars($subtitle); ?></p>
    <div class="account-form">
        <?php if (is_null($user)): ?>
        <a href="/account/login?return=<?php
            echo htmlspecialchars(
                urlencode($_SERVER['REQUEST_URI']),
                ENT_QUOTES,
                'UTF-8',
            );
        ?>"><?php echo htmlspecialchars($t['login_button']); ?></a>

        <a href="/account/register?return=<?php
            echo htmlspecialchars(
                urlencode($_SERVER['REQUEST_URI']),
                ENT_QUOTES,
                'UTF-8',
            );
        ?>"><?php echo htmlspecialchars($t['register_button']); ?></a>
        <?php endif ?>

        <a href="/account/preferences"><?php
            echo htmlspecialchars($t['preferences_button']);
        ?></a>

        <?php if (!is_null($user)): ?>
        <form method="POST" action="/account/logout">
            <input type="hidden" name="return" value="<?php
                echo htmlspecialchars(
                    urlencode($_SERVER['REQUEST_URI']),
                    ENT_QUOTES,
                    'UTF-8',
                ); ?>">
            <button type="submit" class="align-left">
                <?php echo htmlspecialchars($t['logout_button']); ?>
            </button>
        </form>
        <?php endif ?>
    </div>
<?php } ?>
