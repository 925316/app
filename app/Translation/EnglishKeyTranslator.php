<?php

namespace App\Translation;

use Illuminate\Translation\Translator;

class EnglishKeyTranslator extends Translator
{
    /**
     * Get the translation for the given key.
     *
     * @param  array<string, string>  $replace
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $locale = $locale ?: $this->locale();

        if (is_string($key) && ! str_contains($key, '.')) {
            $line = $this->getLineFromMessagesGroup($key, $locale, $fallback);

            if (is_string($line)) {
                return $this->makeReplacements($line, $replace);
            }
        }

        return parent::get($key, $replace, $locale, $fallback);
    }

    private function getLineFromMessagesGroup(string $key, string $locale, bool $fallback): ?string
    {
        $locales = [$locale];

        if ($fallback) {
            $fallbackLocale = $this->getFallback();

            if (is_string($fallbackLocale) && $fallbackLocale !== '' && ! in_array($fallbackLocale, $locales, true)) {
                $locales[] = $fallbackLocale;
            }
        }

        foreach ($locales as $currentLocale) {
            $lines = $this->loader->load($currentLocale, 'messages', '*');

            if (isset($lines[$key]) && is_string($lines[$key])) {
                return $lines[$key];
            }
        }

        return null;
    }
}
