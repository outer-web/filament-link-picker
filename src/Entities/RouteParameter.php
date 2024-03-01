<?php

namespace Outerweb\FilamentLinkPicker\Entities;

class RouteParameter
{
    public function __construct(
        public ?string $name = null,
        public ?string $label = null,
        public string $type = 'text',
        public ?string $model = null,
        public ?string $modelRouteKeyName = null,
        public bool $is_required = false,
        public array $options = [],
    ) {
        //
    }

    public static function make(
        ?string $name = null,
        ?string $label = null,
        string $type = 'text',
        ?string $model = null,
        ?string $modelRouteKeyName = null,
        bool $isRequired = false,
        array $options = [],
    ): static {
        return new static($name, $label, $type, $model, $modelRouteKeyName, $isRequired, $options);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'model' => $this->model,
            'model_route_key_name' => $this->modelRouteKeyName,
            'is_required' => $this->is_required,
            'options' => $this->options,
        ];
    }
}
