<?php

namespace Outerweb\FilamentLinkPicker\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Outerweb\FilamentLinkPicker\Entities\Link as EntitiesLink;

class LinkCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return EntitiesLink::makeFromArray($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        return $value->toArray();
    }
}
