<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileLocaleUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->hasPrivilege(7); // Admin privilege level

        $supportedLocales = (array) config('app.supported_locales', []);
        $currentLocale = app()->getLocale();

        return view('profile.edit', [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'supportedLocales' => $supportedLocales,
            'currentLocale' => $currentLocale,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasPrivilege(7);

        // Only admin can update username and email
        $validated = $request->validated();
        if (! $isAdmin) {
            unset($validated['username'], $validated['email']);
        }

        unset($validated['locale']);

        $user->fill($validated);

        if (isset($validated['email']) && $user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's language preference.
     */
    public function updateLocale(ProfileLocaleUpdateRequest $request): RedirectResponse
    {
        $locale = (string) $request->validated('locale');
        $sessionKey = (string) config('app.locale_session_key', 'locale');

        $request->session()->put($sessionKey, $locale);

        return Redirect::back()
            ->with('status', 'locale-updated')
            ->with('locale-updated-value', $locale)
            ->withCookie(
                cookie(
                    (string) config('app.locale_cookie_name', 'locale'),
                    $locale,
                    (int) config('app.locale_cookie_lifetime', 525600)
                )
            );
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
