<?php

namespace Marble\Admin\Components;

use Illuminate\View\Component;
use Marble\Admin\Models\Item;

class MarbleChildren extends Component
{
    public function __construct(
        public Item $item,
        public ?string $blueprint = null,
        public string $status = 'published',
        public int $limit = 0,
    ) {}

    public function children()
    {
        $query = $this->item->children()
            ->where('status', $this->status)
            ->with('blueprint');

        if ($this->blueprint) {
            $query->whereHas('blueprint', fn ($q) => $q->where('identifier', $this->blueprint));
        }

        if ($this->limit > 0) {
            $query->limit($this->limit);
        }

        return $query->get();
    }

    public function render()
    {
        return view('marble::components.marble-children');
    }
}
