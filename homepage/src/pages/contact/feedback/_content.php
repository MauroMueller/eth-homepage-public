<?php
return function() use ($page) { ?>
    <?php $t = App::lang()->page($page['current_page']); ?>
    <h1><?php echo htmlspecialchars($t['h1']); ?></h1>
    <?php
        $messages = $_SESSION['validation_messages'] ?? [];
        unset($_SESSION['validation_messages']);
        
        foreach ($messages as $message) {
            $message = FlashMessage::from_array($message)->message();
            echo htmlspecialchars($message) . '<br>';
        }
    ?>
    <form method="post" action="/contact/feedback" class="contact-form">
        <input
            type="email"
            name="email"
            placeholder="<?php echo htmlspecialchars($t['email_optional']); ?>"
            autocomplete="email"
        >
        
        <div class="checkbox-div">
            <label class="request-reply">
                <input type="checkbox" name="request_reply">
                <?php echo htmlspecialchars($t['request_reply']); ?>
            </label>
        </div>
        
        <textarea
            name="content"
            rows=10
            placeholder="<?php echo htmlspecialchars($t['content']); ?>"
            required
        ></textarea>

        <button type="submit">
            <?php echo htmlspecialchars($t['form_button']); ?>
        </button>
    </form>
<?php } ?>
