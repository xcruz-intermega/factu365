<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MailConfiguration;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminMailController extends Controller
{
    public function __construct(
        private MailConfigService $mailConfigService,
    ) {}

    public function show()
    {
        $config = MailConfiguration::first();

        return Inertia::render('Admin/MailSettings', [
            'mailConfig' => $config ? [
                'host' => $config->host,
                'port' => $config->port,
                'username' => $config->username,
                'encryption' => $config->encryption,
                'from_address' => $config->from_address,
                'from_name' => $config->from_name,
                'is_active' => $config->is_active,
                'tested_at' => $config->tested_at?->toIso8601String(),
                'has_password' => !empty($config->getRawOriginal('password')),
            ] : null,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'host' => ['required_if:is_active,true', 'nullable', 'string', 'max:255'],
            'port' => ['required_if:is_active,true', 'nullable', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'from_address' => ['required_if:is_active,true', 'nullable', 'email', 'max:255'],
            'from_name' => ['required_if:is_active,true', 'nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $existing = MailConfiguration::first();

        // If password is empty and config already exists, keep the current password
        if (empty($validated['password']) && $existing) {
            unset($validated['password']);
        }

        $config = MailConfiguration::updateOrCreate(
            ['id' => $existing?->id ?? 1],
            $validated,
        );

        $config->clearCache();

        // Re-apply config at runtime
        $this->mailConfigService->applyConfig();

        return back()->with('success', trans('admin.mail_settings_saved'));
    }

    public function test(Request $request)
    {
        $validated = $request->validate([
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        // Build a temporary config object for testing
        $config = new MailConfiguration($validated);

        // If no password provided, use existing one
        $existing = MailConfiguration::first();
        if (empty($validated['password']) && $existing) {
            $config->password = $existing->password;
        }

        $result = $this->mailConfigService->testConnection($config);

        if ($result === true) {
            // Update tested_at on the saved config
            if ($existing) {
                $existing->update(['tested_at' => now()]);
                MailConfiguration::clearCache();
            }

            return back()->with('success', trans('admin.mail_settings_test_success'));
        }

        return back()->with('error', trans('admin.mail_settings_test_failed', ['error' => $result]));
    }
}
