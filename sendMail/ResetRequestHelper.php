<?php

namespace sendMail;

require(__DIR__ . '/../vendor/autoload.php');

function sendResetRequestEmail(string $toEmail, string $token)
{
    $senderEmail = 'takaki_5573031@yahoo.co.jp';
    $subject = 'パスワードリセットリクエスト';

    $resetLink = "http://localhost:3000/views/users/auth/reset_password.php?token=" . $token;
    $emailBody = "パスワードリセットリクエストを受け付けました。以下のリンクをクリックしてパスワードをリセットしてください。\n\n" . $resetLink;

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($senderEmail, 'Takaproject');
    $email->setSubject($subject);
    $email->addTo($toEmail);
    $email->addContent("text/plain", $emailBody);

    define('SENDGRID_API_KEY', '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');

    $sendgridApiKey = SENDGRID_API_KEY;

    $sendgrid = new \SendGrid($sendgridApiKey);

    try {
        $response = $sendgrid->send($email);

        // メールが正常に送信されたかどうかを確認
        if ($response->statusCode() == 202) {
            $_SESSION['reset_request_message'] = '指定のメールアドレスにパスワード再設定のリンクを送信しました。';
        }
    } catch (Exception $e) {
        error_log('SendGrid Error: ' . $e->getMessage());

        // ユーザーには一般的なエラーメッセージを表示
        echo '予期せぬエラーが発生しました。';
    }
}
