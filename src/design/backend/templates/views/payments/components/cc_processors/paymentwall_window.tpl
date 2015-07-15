<div class="form-field">
    <label for="key">{__("project_key")}:</label>
    <input type="text" name="payment_data[processor_params][key]" id="key" value="{$processor_params.key}"
           class="input-text"/>
</div>
<div class="form-field">
    <label for="secret">{__("secret_key")}:</label>
    <input type="text" name="payment_data[processor_params][secret]" id="secret" value="{$processor_params.secret}"
           class="input-text"/>
</div>
<div class="form-field">
    <label for="widget_type">{__("widget_code")}:</label>
    <input type="text" name="payment_data[processor_params][widget_type]" id="widget_type"
           value="{$processor_params.widget_type}" class="input-text"/>
</div>
<div class="form-field">
    <label for="widget_type">{__("test_mode")}:</label>
    <select name="payment_data[processor_params][test_mode]" id="test_mode" class="input-text">
        <option value="0" {if $processor_params.test_mode == "0"}selected="selected"{/if}>{__("No")}</option>
        <option value="1" {if $processor_params.test_mode == "1"}selected="selected"{/if}>{__("Yes")}</option>
    </select>
</div>
<div class="form-field">
    <label for="widget_type">{__("enable_delivery_confirmation")}:</label>
    <select name="payment_data[processor_params][enable_delivery]" id="enable_delivery" class="input-text">
        <option value="0" {if $processor_params.enable_delivery == "0"}selected="selected"{/if}>{__("No")}</option>
        <option value="1" {if $processor_params.enable_delivery == "1"}selected="selected"{/if}>{__("Yes")}</option>
    </select>
</div>

