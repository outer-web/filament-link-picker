<?php

namespace Outerweb\FilamentLinkPicker\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Outerweb\FilamentLinkPicker\Entities\Link as EntitiesLink;

class LinkCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return EntitiesLink::makeFromArray($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof EntitiesLink) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }
}
