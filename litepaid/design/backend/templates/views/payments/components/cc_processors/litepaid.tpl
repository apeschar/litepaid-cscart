<div class="control-group">
    <label class="control-label cm-required" for="litepaid-api-key">API key:</label>
    <div class="controls">
        <input id="litepaid-api-key" type="text" name="payment_data[processor_params][api_key]" value="{$processor_params.api_key}">
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("litepaid_test_mode")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="checkbox" name="payment_data[processor_params][test_mode]" value="1" {if $processor_params.test_mode}checked{/if}>
            {__("litepaid_test_mode_description")}
        </label>
    </div>
</div>
