<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/pages/account/account-form.css',
]);

return function() use ($page) { ?>
    <?php
        $t = App::lang()->page($page['current_page']);
        $token_type = $_GET['token_type'] ?? $_SESSION['pending_token_type']
                                          ?? null;
        $token = $_GET['token'] ?? $_SESSION['pending_token'] ?? null;
        unset($_SESSION['pending_token_type']);
        unset($_SESSION['pending_token']);
        if (is_null($token_type))
            $token_type = 'none';
        $t = $t[$token_type];
    ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>
    <p><?php echo htmlspecialchars($t['paragraph']); ?></p>
    <?php
        $messages = $_SESSION['validation_messages'] ?? [];
        unset($_SESSION['validation_messages']);
        
        foreach ($messages as $message)
            echo htmlspecialchars($message) . '<br>';
    ?>
    <?php if ($token_type != 'none'): ?>
        <form method="post" action="/account/verify" class="account-form">
            <input type="hidden" name="token_type"
                value="<?php echo htmlspecialchars($token_type); ?>">
            <input type="hidden" name="token"
                value="<?php echo htmlspecialchars($token); ?>">
            
            <?php if ($token_type == 'password_reset'): ?>
                <input
                    type="text"
                    name="identifier"
                    placeholder="<?php
                        echo htmlspecialchars($t['identifier']);
                    ?>"
                    autocomplete="username"
                    required
                >
                <input
                    type="password"
                    name="password"
                    placeholder="<?php
                        echo htmlspecialchars($t['new_password']);
                    ?>"
                    autocomplete="new-password"
                    required
                >
                <input
                    type="password"
                    name="password_confirm"
                    placeholder="<?php
                        echo htmlspecialchars($t['new_password_confirm']);
                    ?>"
                    autocomplete="new-password"
                    required
                >
            <?php endif ?>

            <button type="submit">
                <?php echo htmlspecialchars($t['form_button']); ?>
            </button>
        </form>
    <?php endif ?>
<?php } ?>
