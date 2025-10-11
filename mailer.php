<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendLevelMindsMail(string $subject, string $body, ?string $replyTo = null, ?string $replyName = null, ?string $toEmail = null, ?string $toName = null): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER') ?: 'support@levelminds.in';
        $mail->Password   = getenv('SMTP_PASS') ?: 'Levelminds@2024';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int)(getenv('SMTP_PORT') ?: 465);

        $fromEmail = getenv('SMTP_FROM') ?: 'support@levelminds.in';
        $fromName  = getenv('SMTP_FROM_NAME') ?: 'LevelMinds';
        $mail->setFrom($fromEmail, $fromName);

        $recipientEmail = $toEmail ?: getenv('SMTP_TO') ?: $fromEmail;
        $recipientName  = $toName ?: getenv('SMTP_TO_NAME') ?: $fromName;
        $mail->addAddress($recipientEmail, $recipientName);

        if ($replyTo) {
            $mail->addReplyTo($replyTo, $replyName ?: $replyTo);
        }

        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
