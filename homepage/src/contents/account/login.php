<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/pages/account/account-form.css',
]);

return function() use ($page) { ?>
    <?php $t = App::lang()->page($page['current_page']); ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>
    <?php
        $messages = $_SESSION['validation_messages'] ?? [];
        unset($_SESSION['validation_messages']);
        
        foreach ($messages as $message)
            echo htmlspecialchars($message) . '<br>';
    ?>
    <form method="post" action="/account/login" class="account-form">
        <input type="hidden" name="return"
            value="<?php echo htmlspecialchars(
                $_GET['return'] ?? safe_relative_url(null)
            ); ?>">

        <input
            type="text"
            name="identifier"
            placeholder="<?php echo htmlspecialchars($t['identifier']); ?>"
            autocomplete="username"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="<?php echo htmlspecialchars($t['password']); ?>"
            autocomplete="current-password"
            required
        >

        <button type="submit">
            <?php echo htmlspecialchars($t['form_button']); ?>
        </button>
    </form>

    <div class="spacer half"></div>

    <div class="account-form">
        <a class="align-center"
            href="/account/reset-password"><?php
                echo htmlspecialchars($t['reset_pw_button']);
            ?></a>
    </div>
<?php } ?>
