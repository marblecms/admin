@php
    $itemBundles = \Marble\Admin\Models\ContentBundle::whereHas('bundleItems', fn($q) => $q->where('item_id', $item->id))
        ->orderByDesc('updated_at')->get();
@endphp

@if($itemBundles->isNotEmpty())
<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'package']) {{ trans('marble::admin.bundles') }}</h2>
        </div>
        <div class="profile-box-content clearfix">
            <ul class="menu-items">
                @foreach($itemBundles as $b)
                    <li>
                        <a href="{{ route('marble.bundle.show', $b) }}" class="clearfix">
                            @php $statusColors = ['draft' => 'default', 'published' => 'success', 'rolled_back' => 'warning']; @endphp
                            <span class="label label-{{ $statusColors[$b->status] ?? 'default' }}" style="margin-right:4px;">{{ $b->status }}</span>
                            {{ $b->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif
