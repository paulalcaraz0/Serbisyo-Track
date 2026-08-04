<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var array<string, string> $locales */
        $locales = config('serbisyo.locales');

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(array_keys($locales))],
            'redirect_to' => ['nullable', 'string', 'max:2048', 'regex:#^/(?!/)[^\r\n]*$#'],
        ]);

        $request->session()->put('locale', $validated['locale']);

        return redirect($validated['redirect_to'] ?? route('home', absolute: false), 303);
    }
}
