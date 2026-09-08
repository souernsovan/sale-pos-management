<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = [
            'shop_name' => Setting::get('shop_name', config('app.name')),
            'shop_address' => Setting::get('shop_address'),
            'shop_logo' => Setting::get('shop_logo'),
            'site_title' => Setting::get('site_title', config('app.name')),
            'site_description' => Setting::get('site_description', 'Point of sale management system'),
            'site_icon' => Setting::get('site_icon'),
            'currency_symbol' => Setting::get('currency_symbol', '$'),
            'tax_rate' => Setting::get('tax_rate', '0'),
            'low_stock_threshold' => Setting::get('low_stock_threshold', '5'),
            'bakong_account_id' => Setting::get('bakong_account_id'),
            'bakong_account_name' => Setting::get('bakong_account_name'),
            'bakong_merchant_city' => Setting::get('bakong_merchant_city', 'Phnom Penh'),
        ];

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shop_name' => ['required', 'string', 'max:255'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'shop_logo' => ['nullable', 'image', 'max:1024'],
            'site_title' => ['sometimes', 'string', 'max:255'],
            'site_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'site_icon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:512'],
            'remove_site_icon' => ['nullable', 'boolean'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'bakong_account_id' => ['nullable', 'string', 'max:32'],
            'bakong_account_name' => ['nullable', 'string', 'max:25'],
            'bakong_merchant_city' => ['nullable', 'string', 'max:15'],
        ]);

        if ($request->hasFile('shop_logo')) {
            $oldLogo = Setting::get('shop_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            $data['shop_logo'] = $request->file('shop_logo')->store('settings', 'public');
        } else {
            unset($data['shop_logo']);
        }

        if ($request->hasFile('site_icon')) {
            $oldIcon = Setting::get('site_icon');
            if ($oldIcon) {
                Storage::disk('public')->delete($oldIcon);
            }
            $data['site_icon'] = $request->file('site_icon')->store('settings', 'public');
        } elseif ($request->boolean('remove_site_icon')) {
            $oldIcon = Setting::get('site_icon');
            if ($oldIcon) {
                Storage::disk('public')->delete($oldIcon);
            }
            $data['site_icon'] = null;
        } else {
            unset($data['site_icon']);
        }

        unset($data['remove_site_icon']);

        $changed = [];

        foreach ($data as $key => $value) {
            $before = Setting::get($key);
            $after = (string) $value;

            if ($before !== $after) {
                $changed[$key] = ['before' => $before, 'after' => $after];
            }

            Setting::set($key, $after);
        }

        if (! empty($changed)) {
            Audit::log('settings', 'Updated shop settings ('.implode(', ', array_keys($changed)).')', properties: ['changes' => $changed], event: 'updated');
        }

        return redirect()->route('settings.edit')->with('status', 'Settings updated.');
    }
}
