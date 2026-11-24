<?php
function process_feedback($email, $request_reply, $content) {
    $result = new ValidationResult(ValidationAction::FEEDBACK);

    if ($request_reply && !$email)
        $result->add(ValidationError::REQUIRED, ValidationField::EMAIL);
    if (!$content)
        $result->add(ValidationError::REQUIRED, ValidationField::FEEDBACK);
    if (!$result->no_issues()) return $result;

    $mail_text = App::lang()->mail_text('feedback');
    if ($request_reply) $mail_text['subject'] .= $mail_text['request_reply'];
    $mail_text['body'] = $content;

    App::mailer()->send(
        App::config()->mail()['feedback_receiver'],
        $mail_text['subject'],
        $mail_text['body'],
        [
            'default_addresses' => [
                'set_replyto'       => $email ? true : false,
                'replyto_address'   => $email,
                'replyto_name'      => null,
            ],
        ],
    );

    return $result;
}

$result = process_feedback($_POST['email'], $_POST['request_reply'] ?? null, $_POST['content']);

$_SESSION['validation_messages'] = $result->messages();
header('Location: /contact/feedback');
?>
