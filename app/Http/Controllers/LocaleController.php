<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'locale' => ['required', 'in:'.implode(',', SetLocale::SUPPORTED_LOCALES)],
        ]);

        session(['locale' => $request->string('locale')->value()]);

        return back();
    }
}
