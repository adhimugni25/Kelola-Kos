<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait TenantScope
{
    protected static function bootTenantScope(): void
    {
        static::addGlobalScope('kos_id', function (Builder $builder): void {
            $user = auth()->user();

            if ($user?->role !== 'pengurus' || $user->kos_id === null) {
                return;
            }

            /** @var Model $model */
            $model = $builder->getModel();

            $builder->where($model->getTable().'.kos_id', $user->kos_id);
        });
    }
}
