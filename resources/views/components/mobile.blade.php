@php
    // Retrieve model bound value or old input if $value is null
    $rawVal = $value;
    if (is_null($rawVal) && class_exists('\Form') && \Form::getModel()) {
        $model = \Form::getModel();
        $rawVal = is_object($model) ? ($model->{$name} ?? null) : null;
    }
    if (is_null($rawVal)) {
        $rawVal = old($name);
    }

    $selectedCountryCode = '+91';
    $displayValue = $rawVal;

    if (!empty($rawVal)) {
        $displayValue = trim($rawVal);
        $codes = ['+971', '+880', '+966', '+977', '+91', '+44', '+61', '+49', '+33', '+81', '+65', '+92', '+94', '+1', '+7'];
        
        $foundCode = false;
        while (str_starts_with($displayValue, '+')) {
            $stripped = false;
            foreach ($codes as $code) {
                if (str_starts_with($displayValue, $code)) {
                    if (!$foundCode) {
                        $selectedCountryCode = $code;
                        $foundCode = true;
                    }
                    $displayValue = trim(substr($displayValue, strlen($code)));
                    $stripped = true;
                    break;
                }
            }
            if (!$stripped) {
                $displayValue = trim(ltrim($displayValue, '+'));
                break;
            }
        }
    }
@endphp

<style>
    .phone-number-wrapper .input-group-text-select {
        max-width: 125px;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        background-color: #f8f9fa;
        font-weight: 500;
    }
    .phone-input-help {
        font-size: 11px;
        color: #6c757d;
        margin-top: 4px;
        line-height: 1.2;
    }
</style>

<div class="{{ $divClass }}">
    <div class="form-group">
        {{ Form::label($name, $label, ['class' => 'form-label']) }}@if($required) <x-required></x-required> @endif
        <div class="phone-number-wrapper">
            <div class="input-group">
                <select name="country_code" class="form-select input-group-text-select" id="country_code_select">
                    <option value="+91"  {{ $selectedCountryCode == '+91'  ? 'selected' : '' }}>IN +91</option>
                    <option value="+44"  {{ $selectedCountryCode == '+44'  ? 'selected' : '' }}>UK +44</option>
                    <option value="+1"   {{ $selectedCountryCode == '+1'   ? 'selected' : '' }}>US +1</option>
                    <option value="+61"  {{ $selectedCountryCode == '+61'  ? 'selected' : '' }}>AU +61</option>
                    <option value="+971" {{ $selectedCountryCode == '+971' ? 'selected' : '' }}>AE +971</option>
                    <option value="+1"   {{ $selectedCountryCode == '+1'   ? 'selected' : '' }}>CA +1</option>
                    <option value="+49"  {{ $selectedCountryCode == '+49'  ? 'selected' : '' }}>DE +49</option>
                    <option value="+33"  {{ $selectedCountryCode == '+33'  ? 'selected' : '' }}>FR +33</option>
                    <option value="+81"  {{ $selectedCountryCode == '+81'  ? 'selected' : '' }}>JP +81</option>
                    <option value="+966" {{ $selectedCountryCode == '+966' ? 'selected' : '' }}>SA +966</option>
                    <option value="+65"  {{ $selectedCountryCode == '+65'  ? 'selected' : '' }}>SG +65</option>
                    <option value="+92"  {{ $selectedCountryCode == '+92'  ? 'selected' : '' }}>PK +92</option>
                    <option value="+880" {{ $selectedCountryCode == '+880' ? 'selected' : '' }}>BD +880</option>
                    <option value="+94"  {{ $selectedCountryCode == '+94'  ? 'selected' : '' }}>LK +94</option>
                    <option value="+977" {{ $selectedCountryCode == '+977' ? 'selected' : '' }}>NP +977</option>
                </select>
                <input type="text"
                       name="{{ $name }}"
                       id="{{ $id ?? 'phone_mobile' }}"
                       value="{{ $displayValue }}"
                       class="{{ trim($class . ' phone-input') }}"
                       placeholder="{{ $placeholder }}"
                       @if($required) required @endif
                       autocomplete="tel" />
            </div>
            <div id="phone-mobile-help" class="phone-input-help">
                {{ __('Please use with country code. (ex. +91)') }}
            </div>
        </div>
    </div>
</div>
