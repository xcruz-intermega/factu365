<?php

namespace App\Http\Middleware;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = tenant();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'can' => [
                    'manage_settings' => $request->user()?->isAdmin() ?? false,
                    'manage_users' => $request->user()?->isAdmin() ?? false,
                    'create_edit' => $request->user()?->isAccountant() ?? false,
                ],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'overdue_count' => function () use ($request) {
                try {
                    return $request->user()
                        ? Document::where('document_type', 'invoice')
                            ->where('direction', 'issued')
                            ->where('status', 'overdue')
                            ->count()
                        : 0;
                } catch (\Illuminate\Database\QueryException) {
                    return 0;
                }
            },
            'locale' => app()->getLocale(),
            'available_locales' => ['es', 'en', 'ca'],
            'tenant' => $tenant ? ['slug' => $tenant->slug] : null,
            'app_version' => config('app.version'),
        ];
    }
}
