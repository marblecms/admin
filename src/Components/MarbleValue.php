<?php

namespace Marble\Admin\Components;

use Illuminate\View\Component;
use Marble\Admin\Models\Item;

class MarbleValue extends Component
{
    public function __construct(
        public Item $item,
        public string $field,
        public string $tag = 'span',
        public string|int|null $language = null,
    ) {}

    public function value(): mixed
    {
        return $this->item->value($this->field, $this->language);
    }

    public function render()
    {
        return view('marble::components.marble-value');
    }
}
