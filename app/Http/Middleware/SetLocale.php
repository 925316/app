<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys((array) config('app.supported_locales', []));
        $defaultLocale = (string) config('app.locale', 'en');
        $cookieName = (string) config('app.locale_cookie_name', 'locale');
        $sessionKey = (string) config('app.locale_session_key', 'locale');

        $cookieName = trim($cookieName) !== '' ? $cookieName : 'locale';
        $sessionKey = trim($sessionKey) !== '' ? $sessionKey : 'locale';

        $resolvedLocale = $this->resolveLocale($request, $supportedLocales, $defaultLocale, $cookieName, $sessionKey);
        App::setLocale($resolvedLocale);

        return $next($request);
    }

    /**
     * @param  list<string>  $supportedLocales
     */
    private function resolveLocale(
        Request $request,
        array $supportedLocales,
        string $defaultLocale,
        string $cookieName,
        string $sessionKey
    ): string {
        if (empty($supportedLocales)) {
            return $defaultLocale;
        }

        $sessionLocale = $request->session()->get($sessionKey);
        if (is_string($sessionLocale)) {
            $normalizedSessionLocale = $this->normalizeLocale($sessionLocale);
            $matchedSessionLocale = $this->resolveSupportedLocale($normalizedSessionLocale, $supportedLocales);
            if ($matchedSessionLocale !== null) {
                return $matchedSessionLocale;
            }
        }

        $cookieLocale = $request->cookie($cookieName);
        if (is_string($cookieLocale)) {
            $normalizedCookieLocale = $this->normalizeLocale($cookieLocale);
            $matchedCookieLocale = $this->resolveSupportedLocale($normalizedCookieLocale, $supportedLocales);
            if ($matchedCookieLocale !== null) {
                return $matchedCookieLocale;
            }
        }

        $preferredLocales = $request->getLanguages();
        foreach ($preferredLocales as $preferredLocale) {
            $normalizedLocale = $this->normalizeLocale($preferredLocale);
            $matchedPreferredLocale = $this->resolveSupportedLocale($normalizedLocale, $supportedLocales);
            if ($matchedPreferredLocale !== null) {
                return $matchedPreferredLocale;
            }
        }

        $matchedDefaultLocale = $this->resolveSupportedLocale($defaultLocale, $supportedLocales);

        return $matchedDefaultLocale ?? $supportedLocales[0];
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = str_replace('-', '_', $locale);

        if (Str::contains($locale, '_')) {
            [$language, $region] = explode('_', $locale, 2);

            return Str::lower($language).'_'.Str::upper($region);
        }

        return Str::lower($locale);
    }

    /**
     * @param  list<string>  $supportedLocales
     */
    private function resolveSupportedLocale(string $locale, array $supportedLocales): ?string
    {
        if (in_array($locale, $supportedLocales, true)) {
            return $locale;
        }

        $language = Str::before($locale, '_');
        $exactLanguageMatch = Arr::first(
            $supportedLocales,
            fn (string $supportedLocale): bool => $supportedLocale === $language
        );

        if (is_string($exactLanguageMatch)) {
            return $exactLanguageMatch;
        }

        $regionalMatch = Arr::first(
            $supportedLocales,
            fn (string $supportedLocale): bool => Str::startsWith($supportedLocale, $language.'_')
        );

        if (is_string($regionalMatch)) {
            return $regionalMatch;
        }

        return null;
    }
}
