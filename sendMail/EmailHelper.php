<?php

namespace sendMail;

function sendRegistrationEmail(string $toEmail)
{
    $senderEmail = 'takaki_5573031@yahoo.co.jp';
    $subject = 'ユーザー登録完了';

    $emailBody = "ユーザー登録が完了しました。ありがとうございます。";

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
            echo 'メールが正常に送信されました。';
        }
    } catch (Exception $e) {
        error_log('SendGrid Error: ' . $e->getMessage());

        // ユーザーには一般的なエラーメッセージを表示
        echo '予期せぬエラーが発生しました。';
    }
}
