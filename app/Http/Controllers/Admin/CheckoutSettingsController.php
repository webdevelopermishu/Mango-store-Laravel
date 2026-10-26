<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCheckoutSettingsRequest;
use App\Models\Setting;
use App\Support\CheckoutSettings;
use Illuminate\Http\RedirectResponse;

class CheckoutSettingsController extends Controller
{
    public function edit()
    {
        $fees = CheckoutSettings::fees();
        $methodsCsv = Setting::read('checkout.methods', implode(',', config('store.checkout.default_methods')));

        return view('admin.checkout-settings', [
            'values' => [
                'fee_inside_dhaka' => number_format($fees['inside_dhaka'] / 100, 2, '.', ''),
                'fee_outside_dhaka' => number_format($fees['outside_dhaka'] / 100, 2, '.', ''),
                'methods' => explode(',', (string) $methodsCsv),
                'bkash_number' => CheckoutSettings::paymentNumber('bkash'),
                'nagad_number' => CheckoutSettings::paymentNumber('nagad'),
                'instructions_bn' => Setting::read('checkout.instructions_bn'),
                'instructions_en' => Setting::read('checkout.instructions_en'),
            ],
        ]);
    }

    public function update(StoreCheckoutSettingsRequest $request): RedirectResponse
    {
        foreach (CheckoutSettings::ZONES as $zone) {
            Setting::write("checkout.fee_{$zone}_paisa", (string) (int) round((float) $request->input("fee_{$zone}") * 100));
        }
        Setting::write('checkout.methods', implode(',', $request->input('methods')));
        Setting::write('checkout.bkash_number', $request->input('bkash_number'));
        Setting::write('checkout.nagad_number', $request->input('nagad_number'));
        Setting::write('checkout.instructions_bn', $request->input('instructions_bn'));
        Setting::write('checkout.instructions_en', $request->input('instructions_en'));

        return redirect()->route('admin.checkout.edit')->with('status', 'Checkout settings saved.');
    }
}
