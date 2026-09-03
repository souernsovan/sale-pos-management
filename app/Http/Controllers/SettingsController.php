<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
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
        ];

        $users = User::with('roles')->orderBy('name')->get();

        return view('settings.edit', compact('settings', 'users'));
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

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
        }

        return redirect()->route('settings.edit')->with('status', 'Settings updated.');
    }
}
