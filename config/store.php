<?php

return [
    // Defaults used until the admin uploads or edits branding in Website Settings.
    'name' => env('STORE_NAME', 'Mango Store'),
    'meta_description_default' => env('STORE_META_DESCRIPTION', 'Premium Bangladeshi mangoes, gur, ghee, achar and pumpkin bori, delivered fresh.'),

    'locales' => ['bn', 'en'],
    'default_locale' => 'bn',

    // Order times are stored in UTC and shown in this time zone.
    'timezone' => 'Asia/Dhaka',

    // Shown only when a product or category has no text in either language.
    'untranslated_placeholder' => '—',

    'cart' => [
        'max_qty_per_line' => 50,
    ],

    // Starting values only. The owner sets real fees and payment numbers in Admin → Checkout.
    'checkout' => [
        'delivery_fees' => [
            'inside_dhaka' => 6000,   // paisa (৳60)
            'outside_dhaka' => 12000, // paisa (৳120)
        ],
        'default_methods' => ['cod'],
    ],
];
