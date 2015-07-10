REPLACE INTO cscart_payment_processors (
	processor,
	processor_script,
	processor_template,
	admin_template,
	callback,
	type
)
VALUES (
	'Paymentwall Payment',
	'paymentwall.php',
	'views/orders/components/payments/paymentwall.tpl',
	'paymentwall_window.tpl',
	'Y',
	'P'
);

REPLACE INTO cscart_language_values (lang_code, name, value) VALUES
('EN', 'project_key', 'Project Key')
,('ES', 'project_key', 'Project Key')
,('DE', 'project_key', 'Project Key')
,('FR', 'project_key', 'Project Key')
,('GR', 'project_key', 'Project Key')
,('IT', 'project_key', 'Project Key')
,('NL', 'project_key', 'Project Key')
,('RU', 'project_key', 'Project Key')

,('EN', 'widget_code', 'Widget Code')
,('ES', 'widget_code', 'Widget Code')
,('DE', 'widget_code', 'Widget Code')
,('FR', 'widget_code', 'Widget Code')
,('GR', 'widget_code', 'Widget Code')
,('IT', 'widget_code', 'Widget Code')
,('NL', 'widget_code', 'Widget Code')
,('RU', 'widget_code', 'Widget Code')

,('EN', 'secret_code', 'Secret Code')
,('ES', 'secret_code', 'Secret Code')
,('DE', 'secret_code', 'Secret Code')
,('FR', 'secret_code', 'Secret Code')
,('GR', 'secret_code', 'Secret Code')
,('IT', 'secret_code', 'Secret Code')
,('NL', 'secret_code', 'Secret Code')
,('RU', 'secret_code', 'Secret Code')

,('EN', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
,('ES', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
,('DE', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
,('FR', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
,('GR', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
,('IT', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
,('NL', 'pw_error_widget', 'Oops, something went wrong. Please try again later')
,('RU', 'pw_error_widget', 'Oops, something went wrong. Please try again later')

,('EN', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
,('ES', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
,('DE', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
,('FR', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
,('GR', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
,('IT', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
,('NL', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')
,('RU', 'pw_redirect', 'This page will redirect to checkout in 3 seconds')

,('EN', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
,('ES', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
,('DE', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
,('FR', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
,('GR', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
,('IT', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
,('NL', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API')
,('RU', 'enable_delivery_confirmation', 'Enable Delivery Confirmation API');