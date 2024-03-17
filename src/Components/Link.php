<?php

namespace Outerweb\FilamentLinkPicker\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Outerweb\FilamentLinkPicker\Entities\Link as EntitiesLink;

class Link extends Component
{
    public function __construct(
        public EntitiesLink|array|string|null $link,
        public ?string $label = null,
        public bool $absolute = true,
        public ?string $locale = null,
    ) {
        if (is_string($link)) {
            $this->link = json_decode($link) ?? null;
        }

        if (is_array($link)) {
            $this->link = EntitiesLink::makeFromArray($link);
        }
    }

    public function render() : View|Closure|string
    {
        return view('filament-link-picker::components.link');
    }

    public function shouldRender() : bool
    {
        return ! is_null($this->link);
    }
}
