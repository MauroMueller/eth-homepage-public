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
    <form method="post" action="/account/reset-password" class="account-form">
        <input type="hidden" name="return"
            value="<?php echo htmlspecialchars('/'.$page['current_page']); ?>">

        <input
            type="text"
            name="identifier"
            placeholder="<?php echo htmlspecialchars($t['identifier']); ?>"
            autocomplete="username"
            required
        >

        <button type="submit">
            <?php echo htmlspecialchars($t['form_button']); ?>
        </button>
    </form>
<?php } ?>
