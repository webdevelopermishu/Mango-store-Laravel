@extends('layouts.admin')
@section('title', 'Checkout settings')

@section('content')
<h1>Website settings → Checkout</h1>
<p class="muted">Delivery charges and the payment options customers see at checkout.</p>

<form method="post" action="{{ route('admin.checkout.update') }}" class="admin-form">
    @csrf
    @method('PUT')

    <fieldset>
        <legend>Delivery charges (৳)</legend>
        <div class="row">
            <label class="field"><span>Inside Dhaka</span>
                <input type="number" step="0.01" min="0" name="fee_inside_dhaka" value="{{ old('fee_inside_dhaka', $values['fee_inside_dhaka']) }}" required></label>
            <label class="field"><span>Outside Dhaka</span>
                <input type="number" step="0.01" min="0" name="fee_outside_dhaka" value="{{ old('fee_outside_dhaka', $values['fee_outside_dhaka']) }}" required></label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Payment methods</legend>
        @php $chosen = old('methods', $values['methods']); @endphp
        <label class="checkbox"><input type="checkbox" name="methods[]" value="cod" @checked(in_array('cod', $chosen, true))> Cash on delivery</label>
        <label class="checkbox"><input type="checkbox" name="methods[]" value="bkash" @checked(in_array('bkash', $chosen, true))> bKash (manual "Send Money")</label>
        <label class="field"><span>bKash receiving number</span>
            <input name="bkash_number" value="{{ old('bkash_number', $values['bkash_number']) }}" inputmode="tel" placeholder="01XXXXXXXXX"></label>
        <label class="checkbox"><input type="checkbox" name="methods[]" value="nagad" @checked(in_array('nagad', $chosen, true))> Nagad (manual "Send Money")</label>
        <label class="field"><span>Nagad receiving number</span>
            <input name="nagad_number" value="{{ old('nagad_number', $values['nagad_number']) }}" inputmode="tel" placeholder="01XXXXXXXXX"></label>
        <p class="muted small">Customers enter the number they paid from and the transaction ID. Orders arrive as "Being verified"; mark them Paid after checking your bKash/Nagad statement.</p>
    </fieldset>

    <fieldset>
        <legend>Extra payment instructions (optional)</legend>
        <label class="field"><span>বাংলা</span><textarea name="instructions_bn" rows="2" maxlength="1000">{{ old('instructions_bn', $values['instructions_bn']) }}</textarea></label>
        <label class="field"><span>English</span><textarea name="instructions_en" rows="2" maxlength="1000">{{ old('instructions_en', $values['instructions_en']) }}</textarea></label>
    </fieldset>

    <button class="btn btn--primary" type="submit">Save checkout settings</button>
</form>
@endsection
