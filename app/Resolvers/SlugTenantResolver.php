<?php

declare(strict_types=1);

namespace App\Resolvers;

use App\Models\Tenant;
use Illuminate\Routing\Route;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedByPathException;
use Stancl\Tenancy\Resolvers\PathTenantResolver;

class SlugTenantResolver extends PathTenantResolver
{
    public function resolveWithoutCache(...$args): TenantContract
    {
        /** @var Route $route */
        $route = $args[0];

        if ($slug = $route->parameter(static::$tenantParameterName)) {
            $route->forgetParameter(static::$tenantParameterName);

            if ($tenant = Tenant::where('slug', $slug)->first()) {
                if ($tenant->suspended_at) {
                    abort(403, 'Esta cuenta ha sido suspendida. Contacte con el administrador.');
                }

                return $tenant;
            }
        }

        throw new TenantCouldNotBeIdentifiedByPathException($slug ?? '');
    }
}
