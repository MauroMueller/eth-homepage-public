<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/pages/account/account-form.css',
]);
App::asset_registry()->add_js([
    App::config()->base_url().'/assets/js/pages/account/preferences-form.js',
]);

return function() use ($page) { ?>
    <?php $t = App::lang()->page($page['current_page']); ?>
    <?php $config = App::config()->supported_preferences() ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>
    <?php
        $messages = $_SESSION['validation_messages'] ?? [];
        unset($_SESSION['validation_messages']);
        
        foreach ($messages as $message)
            echo htmlspecialchars($message) . '<br>';
    ?>
    <div class="account-form preferences">
        <h3><?php echo htmlspecialchars($t['subtitle_preferences']); ?></h3>
        <form method="post" action="/account/preferences">
            <input type="hidden" name="section" value="preferences">
            <input type="hidden" name="return"
                value="<?php echo htmlspecialchars('/'.$page['current_page']); ?>">

            <label for="language">
                <?php echo htmlspecialchars($t['language']); ?>
            </label>
            <select id="language" name="language">
                <?php $active_lang = App::preferences()->resolve_language(); ?>
                <?php foreach ($config['language'] as $lang) { ?>
                    <option
                        value="<?php echo htmlspecialchars($lang); ?>"
                        <?php echo $lang === $active_lang ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($t[$lang]); ?>
                    </option>
                <?php } ?>
            </select>

            <label for="timezone" hidden>
                <?php echo htmlspecialchars($t['timezone']); ?>
            </label>
            <select id="timezone" name="timezone" hidden>
                <?php $active_tz = App::preferences()
                                      ->resolve_timezone_name(); ?>
                <?php foreach ($config['timezone'] as $tz) { ?>
                    <option
                        value="<?php echo htmlspecialchars($tz); ?>"
                        <?php echo $tz === $active_tz ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($tz); ?>
                    </option>
                <?php } ?>
            </select>

            <label for="locale" hidden>
                <?php echo htmlspecialchars($t['locale']); ?>
            </label>
            <select id="locale" name="locale" hidden>
                <?php $active_locale = App::preferences()->resolve_locale(); ?>
                <?php foreach ($config['locale'] as $locale) { ?>
                    <option
                        value="<?php echo htmlspecialchars($locale); ?>"
                        <?php echo $locale === $active_locale ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($locale); ?>
                    </option>
                <?php } ?>
            </select>

            <div class="buttons">
                <button type="submit" name="scope" value="session">
                    <?php echo htmlspecialchars($t['form_button_session']); ?>
                </button>
                <?php if (!is_null(App::auth()->user())): ?>
                <button type="submit" name="scope" value="account">
                    <?php echo htmlspecialchars($t['form_button_account']); ?>
                </button>
                <?php endif ?>
            </div>
        </form>
        <?php $user = App::auth()->user() ?>
        <?php if (!is_null($user)): ?>
        <div class="spacer"></div>
        <h3><?php echo htmlspecialchars($t['subtitle_account']); ?></h3>
        <form method="post" action="/account/preferences">
            <input type="hidden" name="section" value="account">
            <input type="hidden" name="return"
                value="<?php echo htmlspecialchars('/'.$page['current_page']); ?>">
            <label for="username">
                <?php echo htmlspecialchars($t['username']); ?>
            </label>
            <input
                type="text"
                name="username"
                value="<?php echo htmlspecialchars($user->username()); ?>"
                placeholder="<?php
                    echo htmlspecialchars($t['username']);
                ?>"
                autocomplete="username"
                disabled
                required
            >
            <div class="buttons same-row edit">
                <button type="button" class="edit">
                    <?php echo htmlspecialchars($t['form_button_change']); ?>
                </button>
            </div>
            <div class="buttons same-row action">
                <button type="button" class="cancel">
                    <?php echo htmlspecialchars($t['form_button_cancel']); ?>
                </button>
                <button type="submit" name="confirm" value="username">
                    <?php echo htmlspecialchars($t['form_button_confirm']); ?>
                </button>
            </div>
        </form>

        <form method="post" action="/account/preferences">
            <input type="hidden" name="section" value="account">
            <input type="hidden" name="return"
                value="<?php echo htmlspecialchars('/'.$page['current_page']); ?>">
            <label for="email">
                <?php echo htmlspecialchars($t['email']); ?>
            </label>
            <div class="email-container">
                <input
                    type="text"
                    name="email"
                    value="<?php echo htmlspecialchars($user->email()); ?>"
                    placeholder="<?php echo htmlspecialchars($t['email']); ?>"
                    autocomplete="email"
                    disabled
                    required
                >
                <?php if (!$user->verified()): ?>
                    <?php if (App::auth()->recent_email_verification_request()): ?>
                    <button disabled title="<?php
                        echo htmlspecialchars($t['email_confirm_wait']);
                    ?>">
                        <i class="fa-regular fa-hourglass"></i>
                    </button>
                    <?php else: ?>
                    <button type="submit" form="confirm-email-form"
                        name="confirm" value="email_verification" title="<?php
                            echo htmlspecialchars($t['email_unconfirmed']);
                        ?>">
                        <i class="fa-regular fa-paper-plane"></i>
                    </button>
                    <?php endif ?>
                <?php else: ?>
                <button disabled title="<?php
                        echo htmlspecialchars($t['email_confirmed']);
                    ?>">
                    <i class="fa-solid fa-envelope-circle-check"></i>
                </button>
                <?php endif ?>
            </div>
            <div class="buttons same-row edit">
                <button type="button" class="edit">
                    <?php echo htmlspecialchars($t['form_button_change']); ?>
                </button>
            </div>
            <div class="buttons same-row action">
                <button type="button" class="cancel">
                    <?php echo htmlspecialchars($t['form_button_cancel']); ?>
                </button>
                <button type="submit" name="confirm" value="email">
                    <?php echo htmlspecialchars($t['form_button_confirm']); ?>
                </button>
            </div>
        </form>
        <?php if (!$user->verified()): ?>
        <form id="confirm-email-form" method="post"
            action="/account/preferences" style="position:absolute;">
            <input type="hidden" name="section" value="account">
            <input type="hidden" name="return"
                value="<?php echo htmlspecialchars('/'.$page['current_page']); ?>">
        </form>
        <?php endif ?>

        <form method="post" action="/account/preferences" class="pw">
            <input type="hidden" name="section" value="account">
            <input type="hidden" name="return"
                value="<?php echo htmlspecialchars('/'.$page['current_page']); ?>">
            <label for="password">
                <?php echo htmlspecialchars($t['password']); ?>
            </label>
            <input
                type="password"
                name="dummy_password"
                class="edit"
                value="************"
                disabled
            >
            <input
                type="password"
                name="old_password"
                class="action"
                placeholder="<?php
                    echo htmlspecialchars($t['old_password']);
                ?>"
                autocomplete="current-password"
                disabled
                required
            >
            <input
                type="password"
                name="password"
                class="action"
                placeholder="<?php
                    echo htmlspecialchars($t['new_password']);
                ?>"
                autocomplete="new-password"
                disabled
                required
            >
            <input
                type="password"
                name="password_confirm"
                class="action"
                placeholder="<?php
                    echo htmlspecialchars($t['new_password_confirm']);
                ?>"
                autocomplete="new-password"
                disabled
                required
            >
            <div class="buttons same-row edit">
                <button type="button" class="edit">
                    <?php echo htmlspecialchars($t['form_button_change']); ?>
                </button>
            </div>
            <div class="buttons same-row action">
                <button type="button" class="cancel">
                    <?php echo htmlspecialchars($t['form_button_cancel']); ?>
                </button>
                <button type="submit" name="confirm" value="password">
                    <?php echo htmlspecialchars($t['form_button_confirm']); ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif ?>
<?php } ?>
