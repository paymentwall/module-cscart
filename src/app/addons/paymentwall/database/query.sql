INSERT INTO cscart_payment_processors (
  processor,
  processor_script,
  processor_template,
  admin_template,
  callback,
  type,
  addon
)
VALUES
  (
    'Paymentwall',
    'paymentwall.php',
    'views/orders/components/payments/paymentwall.tpl',
    'paymentwall.tpl',
    'N',
    'P',
    'paymentwall'
  ),
  (
    'Brick',
    'brick.php',
    'views/orders/components/payments/brick.tpl',
    'brick.tpl',
    'Y',
    'P',
    'paymentwall'
  );

REPLACE INTO cscart_language_values (lang_code, name, value) VALUES
  ('EN', 'project_key', 'Project Key')
  , ('EN', 'widget_code', 'Widget Code')
  , ('EN', 'secret_code', 'Secret Code')
  , ('EN', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
  , ('EN', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
  , ('EN', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
  ,
  ('EN', 'widget_notice', 'Please don\'t click the back button nor refresh your browser. Otherwise, the information you entered will be lost.')
  , ('EN', 'public_key', 'Public Key')
  , ('EN', 'private_key', 'Private Key')
  , ('EN', 'test_public_key', 'Test Public Key')
  , ('EN', 'test_private_key', 'Test Private Key')
  
  , ('RU', 'project_key', 'Ключ проекта')
  , ('RU', 'widget_code', 'Номер виджета')
  , ('RU', 'secret_code', 'Секретный ключ')
  , ('RU', 'pw_error_widget', 'Упс.. Кажется что-то пошло не так, попробуйте, пожалуйста, позже.')
  , ('RU', 'pw_redirect', 'Вы будете перенаправлены через 3 секунды..')
  , ('RU', 'enable_delivery_confirmation', 'Включить Delivery Confirmation API')
  ,
('RU', 'widget_notice', 'Пожалуйста, не обновляйте страницу. Иначе вся введенная вами информация будет удалена.')
  , ('RU', 'private_key', 'Приватный ключ')
  , ('RU', 'test_public_key', 'Тестовый публичный ключ')
  , ('RU', 'test_private_key', 'Тестовый приватный ключ');