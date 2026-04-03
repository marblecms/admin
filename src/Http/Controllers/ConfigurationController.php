<?php

namespace Marble\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Marble\Admin\Models\ItemValue;
use Marble\Admin\Models\Language;
use Marble\Admin\Models\MarbleSetting;
use Illuminate\Support\Facades\DB;

class ConfigurationController extends Controller
{
    public function index()
    {
        $languages = Language::all();
        $settings  = MarbleSetting::allKeyed();

        return view('marble::configuration.index', compact('languages', 'settings'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'frontend_url'      => 'nullable|url|max:255',
            'primary_locale'    => 'required|string|max:8',
            'autosave_interval' => 'required|integer|min:5|max:300',
            'lock_ttl'          => 'required|integer|min:30|max:3600',
            'cache_ttl'         => 'required|integer|min:0|max:86400',
        ]);

        MarbleSetting::set('frontend_url',      $request->input('frontend_url', ''));
        MarbleSetting::set('primary_locale',    $request->input('primary_locale'));
        MarbleSetting::set('uri_locale_prefix', $request->boolean('uri_locale_prefix') ? '1' : '0');
        MarbleSetting::set('autosave',          $request->boolean('autosave') ? '1' : '0');
        MarbleSetting::set('autosave_interval', $request->integer('autosave_interval'));
        MarbleSetting::set('lock_ttl',          $request->integer('lock_ttl'));
        MarbleSetting::set('cache_ttl',         $request->integer('cache_ttl'));

        return redirect()->route('marble.configuration.index')->with('success', trans('marble::admin.configuration_saved'));
    }

    public function saveLanguages(Request $request)
    {
        $active = $request->input('active_languages', []);

        Language::all()->each(function ($lang) use ($active) {
            $lang->update(['is_active' => in_array($lang->id, array_map('intval', $active))]);
        });

        return redirect()->route('marble.configuration.index')->with('success', trans('marble::admin.configuration_saved'));
    }

    public function addLanguage(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:8|unique:languages,code',
            'name' => 'required|string|max:100',
        ]);

        Language::create([
            'code'      => strtolower(trim($request->input('code'))),
            'name'      => trim($request->input('name')),
            'is_active' => true,
        ]);

        return redirect()->route('marble.configuration.index')->with('success', trans('marble::admin.language_added'));
    }

    public function deleteLanguage(Language $language)
    {
        if (Language::count() <= 1) {
            return redirect()->route('marble.configuration.index')
                ->withErrors(['delete' => trans('marble::admin.language_last')]);
        }

        // If deleting the primary locale, switch to another language
        $primaryLocale = MarbleSetting::get('primary_locale', config('marble.primary_locale'));
        if ($primaryLocale === $language->code) {
            $fallback = Language::where('id', '!=', $language->id)->first();
            if ($fallback) {
                MarbleSetting::set('primary_locale', $fallback->code);
            }
        }

        DB::transaction(function () use ($language) {
            ItemValue::where('language_id', $language->id)->delete();
            $language->delete();
        });

        return redirect()->route('marble.configuration.index')->with('success', trans('marble::admin.language_deleted'));
    }
}
