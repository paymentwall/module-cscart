<div class="control-group">
    <label class="control-label" for="key">{__("project_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][key]" id="key" value="{$processor_params.key}" >
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="secret">{__("secret_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret]" id="secret" value="{$processor_params.secret}" >
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="widget_type">{__("widget_code")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][widget_type]" id="item_name" value="{$processor_params.widget_type}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="widget_type">{__("test_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][test_mode]" id="test_mode">
            <option value="0" {if $processor_params.test_mode == "0"}selected="selected"{/if}>{__("No")}</option>
            <option value="1" {if $processor_params.test_mode == "1"}selected="selected"{/if}>{__("Yes")}</option>
        </select>
    </div>
</div>