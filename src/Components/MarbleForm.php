<?php

namespace Marble\Admin\Components;

use Illuminate\View\Component;
use Marble\Admin\Models\Item;

class MarbleForm extends Component
{
    public function __construct(
        public Item $item,
        public string $class = '',
        public string $submitLabel = 'Submit',
        public string $submitClass = '',
        public bool $hideSubmit = false,
    ) {}

    public function submitUrl(): string
    {
        return route('marble.form.submit', $this->item);
    }

    public function successMessage(): ?string
    {
        return session('marble_form_success');
    }

    public function render()
    {
        return view('marble::components.marble-form');
    }
}
