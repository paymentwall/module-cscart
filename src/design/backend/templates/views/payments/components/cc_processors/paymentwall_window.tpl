

<div class="form-field">
	<label for="key">{__("project_key")}:</label>
	<input type="text" name="payment_data[processor_params][key]" id="key" value="{$processor_params.key}" class="input-text" />
</div>


<div class="form-field">
	<label for="secret">{__("secret_key")}:</label>
	<input type="text" name="payment_data[processor_params][secret]" id="secret" value="{$processor_params.secret}" class="input-text" />
</div>



<div class="form-field">
	<label for="widget_type">{__("widget_code")}:</label>
	<input type="text" name="payment_data[processor_params][widget_type]" id="widget_type" value="{$processor_params.widget_type}" class="input-text" />
</div>

