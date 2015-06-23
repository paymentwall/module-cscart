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

REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('EN', 'project_key', 'Project Key');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('ES', 'project_key', 'Project Key');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('DE', 'project_key', 'Project Key');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('FR', 'project_key', 'Project Key');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('GR', 'project_key', 'Project Key');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('IT', 'project_key', 'Project Key');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('NL', 'project_key', 'Project Key');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('RU', 'project_key', 'Project Key');

REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('EN', 'widget_code', 'Widget Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('ES', 'widget_code', 'Widget Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('DE', 'widget_code', 'Widget Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('FR', 'widget_code', 'Widget Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('GR', 'widget_code', 'Widget Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('IT', 'widget_code', 'Widget Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('NL', 'widget_code', 'Widget Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('RU', 'widget_code', 'Widget Code');

REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('EN', 'secret_code', 'Secret Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('ES', 'secret_code', 'Secret Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('DE', 'secret_code', 'Secret Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('FR', 'secret_code', 'Secret Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('GR', 'secret_code', 'Secret Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('IT', 'secret_code', 'Secret Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('NL', 'secret_code', 'Secret Code');
REPLACE INTO cscart_language_values (lang_code, name, value) VALUES ('RU', 'secret_code', 'Secret Code');
