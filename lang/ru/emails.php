<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Emails Language Lines
    |--------------------------------------------------------------------------
    */
    /*
        |--------------------------------------------------------------------------
        | Emails Language Lines
        |--------------------------------------------------------------------------
        */

    'email_footer_text' => 'С уважением,',
    'email_footer_text_2' => 'команда :SITE_NAME',
    'email_footer_address' => 'ул. Михаила Максимовича 24, Киев, Украина',

    //user-email-confirmation-email
    'user-email-confirmation-email' => [
        'subject' => 'Подтверждение электронной почты',
        'main_text' => 'Для завершения регистрации, пожалуйста, подтвердите ваш email',
        'call_to_action' => 'Подтвердить email',
        'support_text' => 'Нужна помощь?',
    ],

    //user-forgot-password-email
    'user-forgot-password-email' => [
        'subject' => 'Восстановление пароля',
        'main_text' => 'Ваш пароль успешно восстановлен.',
        'new_password_text' => 'Ваш новый пароль:',
    ],

    //
    'email-subscription-email' => [
        'subject' => 'Подтверждение электронной почты',
        'main_text' => 'Чтобы получить купон на скидку, необходимо подтвердить email.',
        'call_to_action' => 'Подтвердить email',
        'support_text' => 'Нужна помощь?',
    ],

    'email-subscription-confirmed-email' => [
        'subject' => 'Код на скидку',
        'main_text' => 'Ваш код: <strong>:CODE</strong>',
    ],

    'user-credentials-email' => [
        'subject' => 'Автоматическая регистрация',
        'main_text' => 'Вас было автоматически зарегистрировано',
        'email' => 'Email:',
        'password' => 'Пароль:',
    ],

    'admin-notification-email' => [
        'call_to_action' => 'Просмотреть',
    ],

    'your_order' => 'Ваш заказ',
    'table_image' => 'Изображение',
    'table_attributes' => 'Атрибуты',
    'table_sku' => 'Артикул',
    'table_product_name' => 'Название',
    'table_product_count' => 'Количество',
    'table_product_single_price' => 'Цена за единицу',
    'table_product_total_price' => 'Цена',
];
