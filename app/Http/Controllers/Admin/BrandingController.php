<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandingRequest;
use App\Models\Setting;
use App\Support\Branding;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;

class BrandingController extends Controller
{
    private const IMAGE_KEYS = ['logo', 'mobile_logo', 'footer_logo', 'favicon', 'og_image'];

    public function edit()
    {
        $images = [];
        foreach (self::IMAGE_KEYS as $key) {
            $images[$key] = Branding::imageUrl($key);
        }

        return view('admin.branding', [
            'values' => [
                'site_title' => Setting::read('site.title', config('store.name')),
                'tagline_bn' => Setting::read('site.tagline_bn'),
                'tagline_en' => Setting::read('site.tagline_en'),
                'meta_title_template' => Setting::read('site.meta_title_template', '{title} | '.config('store.name')),
                'meta_description_bn' => Setting::read('site.meta_description_bn'),
                'meta_description_en' => Setting::read('site.meta_description_en'),
            ],
            'images' => $images,
        ]);
    }

    public function update(StoreBrandingRequest $request): RedirectResponse
    {
        Setting::write('site.title', $request->string('site_title')->toString());
        Setting::write('site.tagline_bn', $request->input('tagline_bn'));
        Setting::write('site.tagline_en', $request->input('tagline_en'));
        Setting::write('site.meta_title_template', $request->string('meta_title_template')->toString());
        Setting::write('site.meta_description_bn', $request->input('meta_description_bn'));
        Setting::write('site.meta_description_en', $request->input('meta_description_en'));

        $removals = (array) $request->input('remove', []);

        foreach (self::IMAGE_KEYS as $key) {
            $settingKey = "site.{$key}_path";
            $oldPath = Setting::read($settingKey);

            if ($request->hasFile($key)) {
                $newPath = Uploads::replace($oldPath, $request->file($key), 'branding');
                Setting::write($settingKey, $newPath);
            } elseif (in_array($key, $removals, true)) {
                Uploads::remove($oldPath, 'branding');
                Setting::write($settingKey, null);
            }
        }

        return redirect()->route('admin.branding.edit')->with('status', 'Branding saved.');
    }
}
