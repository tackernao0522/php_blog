<?php

function validateProfileInput($full_name, $bio)
{
    $errors = [];

    // フルネームの検証
    if (empty($full_name)) {
        $errors['full_name'] = 'フルネームは必須です。';
    } elseif (mb_strlen($full_name, 'UTF-8') > 25) {
        $errors['full_name'] = 'フルネームは25文字以内で入力して下さい。';
    }

    // 自己紹介の検証
    if (empty($bio)) {
        $errors['bio'] = '自己紹介は必須です。';
    } elseif (mb_strlen($bio, 'UTF-8') > 250) {
        $errors['bio'] = '自己紹介は250文字以内で入力して下さい。';
    }

    return $errors;
}
