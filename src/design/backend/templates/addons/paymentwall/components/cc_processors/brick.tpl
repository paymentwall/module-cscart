<div class="control-group">
    <label class="control-label" for="public_key">{__("public_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][public_key]" id="public_key" value="{$processor_params.public_key}" >
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="private_key">{__("private_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][private_key]" id="secret" value="{$processor_params.private_key}" >
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="test_public_key">{__("test_public_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][test_public_key]" id="test_public_key" value="{$processor_params.test_public_key}" >
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="test_private_key">{__("test_private_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][test_private_key]" id="secret" value="{$processor_params.test_private_key}" >
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