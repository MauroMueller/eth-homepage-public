<?php
/* Load assets */
App::asset_registry()->add_css([
    App::config()->base_url().'/assets/css/pages/account/account-form.css',
]);

return function() use ($page) { ?>
    <?php $t = App::lang()->page($page['current_page']); ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>
    <p class="subtitle more-space">
        <?php echo htmlspecialchars($t['paragraph']); ?>
    </p>
    <?php
        $messages = $_SESSION['validation_messages'] ?? [];
        unset($_SESSION['validation_messages']);
        
        foreach ($messages as $message)
            echo htmlspecialchars($message) . '<br>';
    ?>
    <form method="post" action="/account/register" class="account-form">
        <input type="hidden" name="return"
            value="<?php echo htmlspecialchars(
                $_GET['return'] ?? safe_relative_url(null)
            ); ?>">

        <input
            type="text"
            name="username"
            placeholder="<?php echo htmlspecialchars($t['username']); ?>"
            autocomplete="username"
            required
        >

        <input
            type="text"
            name="email"
            placeholder="<?php echo htmlspecialchars($t['email']); ?>"
            autocomplete="email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="<?php echo htmlspecialchars($t['password']); ?>"
            autocomplete="new-password"
            required
        >

        <input
            type="password"
            name="password_confirm"
            placeholder="<?php
                echo htmlspecialchars($t['password_confirm']);
            ?>"
            autocomplete="new-password"
            required
        >

        <button type="submit">
            <?php echo htmlspecialchars($t['form_button']); ?>
        </button>
    </form>
<?php } ?>
