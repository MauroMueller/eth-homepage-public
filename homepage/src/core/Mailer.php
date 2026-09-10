<?php
/**
 * Manages emails.
 * 
 * Interface for the application to send emails.
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require($BASE_DIR.'/vendor/PHPMailer/src/Exception.php');
require($BASE_DIR.'/vendor/PHPMailer/src/PHPMailer.php');
require($BASE_DIR.'/vendor/PHPMailer/src/SMTP.php');

class Mailer {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function send($to, $subject, $body) {
        $errormsg = null;

        if ($this->config['send']) {
            try {
                $mail = new PHPMailer(true);
                $mail->CharSet = PHPMailer::CHARSET_UTF8;

                $smtp_config = $this->config['smtp'];

                $mail->isSMTP();
                $mail->Host       = $smtp_config['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp_config['username'];
                $mail->Password   = $smtp_config['password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $smtp_config['port'];

                $address_config = $this->config['default_addresses'];

                $mail->setFrom(
                    $address_config['from_address'],
                    $address_config['from_name'],
                );
                $mail->addAddress($to);
                if ($address_config['set_replyto'])
                    $mail->addReplyTo(
                        $address_config['replyto_address'],
                        $address_config['replyto_name'],
                    );
                if ($address_config['set_bcc'])
                    $mail->addBCC($address_config['bcc_address']);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = strip_tags($body);

                $mail->send();
            } catch (Exception $e) {
                $errormsg = '<br>' . $mail->ErrorInfo;
            }
        }

        if ($this->config['log'] || !is_null($errormsg)) {
            $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.html';
            $path = $this->config['log_location'] . '/' . $filename;

            $body .= $errormsg ?? '';
            
            $content =
                '<!DOCTYPE html>' .
                '<html>' .
                '<head>' .
                '<meta charset="UTF-8">' .
                '<title>' . htmlspecialchars($subject) . '</title>' .
                '</head>' .
                '<body>' .
                '<p><strong>To:</strong> ' . htmlspecialchars($to) . '</p>' .
                '<p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>' .
                '<hr>' .
                $body .
                '</body>' .
                '</html>';
            
            file_put_contents($path, $content);
        }
    }
}
?>
