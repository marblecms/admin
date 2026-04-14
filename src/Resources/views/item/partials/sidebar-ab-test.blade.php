@php
    $variant = $item->activeVariant() ?? $item->variants()->first();
    $totalImpressions = $variant ? ($variant->impressions_a + $variant->impressions_b) : 0;
    $pctA = $totalImpressions ? round($variant->impressions_a / $totalImpressions * 100) : 0;
    $pctB = $totalImpressions ? round($variant->impressions_b / $totalImpressions * 100) : 0;
    $splitA = $variant ? (100 - $variant->traffic_split) : 50;
    $splitB = $variant ? $variant->traffic_split : 50;

    // Conversion rate stats
    $minSampleSize = 30; // per bucket before showing results
    $cvrA = ($variant && $variant->impressions_a > 0)
        ? round($variant->conversions_a / $variant->impressions_a * 100, 1) : null;
    $cvrB = ($variant && $variant->impressions_b > 0)
        ? round($variant->conversions_b / $variant->impressions_b * 100, 1) : null;
    $hasEnoughData = $variant
        && $variant->impressions_a >= $minSampleSize
        && $variant->impressions_b >= $minSampleSize;
    $totalConversions = $variant ? ($variant->conversions_a + $variant->conversions_b) : 0;
    // Determine winner (only when enough data and both have impressions)
    $winner = null;
    if ($hasEnoughData && $cvrA !== null && $cvrB !== null && $cvrA !== $cvrB) {
        $winner = $cvrB > $cvrA ? 'b' : 'a';
    }
@endphp

<div class="main-box clearfix profile-box-menu">
    <div class="main-box-body clearfix">
        <div class="profile-box-header gray-bg clearfix">
            <h2>@include('marble::components.famicon', ['name' => 'chart_bar']) {{ trans('marble::admin.ab_test') }}</h2>
        </div>
        <div class="profile-box-content clearfix">
            @if(!$variant)
                <div class="marble-box-body">
                    <p class="text-muted marble-text-sm marble-mb-sm">{{ trans('marble::admin.ab_test') }} — Variant B</p>
                    <form method="POST" action="{{ route('marble.item.variant.create', $item) }}">
                        @csrf
                        <button type="submit" class="btn btn-default btn-sm btn-block">
                            @include('marble::components.famicon', ['name' => 'add']) {{ trans('marble::admin.ab_create_variant') }}
                        </button>
                    </form>
                </div>
            @else
                {{-- Status row --}}
                <div class="ab-status-row">
                    <span class="ab-status-badge {{ $variant->is_active ? 'ab-badge-active' : 'ab-badge-paused' }}">
                        {{ $variant->is_active ? trans('marble::admin.ab_active') : trans('marble::admin.ab_paused') }}
                    </span>
                    <form method="POST" action="{{ route('marble.item.variant.toggle', [$item, $variant]) }}" class="marble-inline-form">
                        @csrf
                        <button type="submit" class="btn btn-xs ab-toggle-btn">
                            {{ $variant->is_active ? trans('marble::admin.ab_paused') : trans('marble::admin.ab_active') }}
                        </button>
                    </form>
                </div>

                {{-- Traffic split bar --}}
                <div class="ab-section ab-split-section">
                    <div class="ab-section-label">{{ trans('marble::admin.ab_traffic_split') }}</div>
                    <div class="ab-split-bar">
                        <div class="ab-split-seg ab-split-a ab-seg-a" data-pct="{{ $splitA }}">
                            <span>A</span>
                        </div>
                        <div class="ab-split-seg ab-split-b ab-seg-b" data-pct="{{ $splitB }}">
                            <span>B</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('marble.item.variant.split', [$item, $variant]) }}" class="ab-split-form">
                        @csrf
                        <div class="ab-split-inputs">
                            <span class="ab-split-label-a ab-lbl-a">A {{ $splitA }}%</span>
                            <input type="range" name="traffic_split" min="1" max="99"
                                   value="{{ $variant->traffic_split }}"
                                   class="ab-split-range ab-split-range-live"
                                   onchange="this.form.submit()" />
                            <span class="ab-split-label-b ab-lbl-b">B {{ $splitB }}%</span>
                        </div>
                    </form>
                </div>

                {{-- Stats --}}
                <div class="ab-section ab-stats-section">
                    <div class="ab-section-label">{{ trans('marble::admin.ab_results') }}</div>
                    @if($totalImpressions > 0)
                        <div class="ab-stats-row">
                            <div class="ab-stat ab-stat-a {{ $winner === 'a' ? 'ab-stat-winner' : '' }}">
                                <div class="ab-stat-label">
                                    A @if($winner === 'a') <span class="ab-winner-badge">✓</span> @endif
                                </div>
                                <div class="ab-stat-pct">{{ $pctA }}%</div>
                                <div class="ab-stat-count">{{ number_format($variant->impressions_a) }} imp.</div>
                                @if($totalConversions > 0)
                                    <div class="ab-stat-cvr">
                                        @if($hasEnoughData)
                                            {{ $cvrA }}% CVR
                                            <span class="ab-stat-cvr-abs">({{ number_format($variant->conversions_a) }})</span>
                                        @else
                                            <span class="text-muted">{{ number_format($variant->conversions_a) }} conv.</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="ab-stat-bar">
                                    <div class="ab-stat-bar-fill ab-stat-bar-a" data-pct="{{ $pctA }}"></div>
                                </div>
                            </div>
                            <div class="ab-stat ab-stat-b {{ $winner === 'b' ? 'ab-stat-winner' : '' }}">
                                <div class="ab-stat-label">
                                    B @if($winner === 'b') <span class="ab-winner-badge">✓</span> @endif
                                </div>
                                <div class="ab-stat-pct">{{ $pctB }}%</div>
                                <div class="ab-stat-count">{{ number_format($variant->impressions_b) }} imp.</div>
                                @if($totalConversions > 0)
                                    <div class="ab-stat-cvr">
                                        @if($hasEnoughData)
                                            {{ $cvrB }}% CVR
                                            <span class="ab-stat-cvr-abs">({{ number_format($variant->conversions_b) }})</span>
                                        @else
                                            <span class="text-muted">{{ number_format($variant->conversions_b) }} conv.</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="ab-stat-bar">
                                    <div class="ab-stat-bar-fill ab-stat-bar-b" data-pct="{{ $pctB }}"></div>
                                </div>
                            </div>
                        </div>
                        <div class="ab-total-label">
                            {{ number_format($totalImpressions) }} impressions
                            @if($totalConversions > 0)
                                · {{ number_format($totalConversions) }} conversions
                            @endif
                        </div>
                        @if($totalConversions > 0 && !$hasEnoughData)
                            <div class="ab-not-enough">
                                @include('marble::components.famicon', ['name' => 'clock'])
                                {{ trans('marble::admin.ab_not_enough_data', ['min' => $minSampleSize]) }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted marble-text-sm ab-no-data">{{ trans('marble::admin.ab_no_impressions') }}</p>
                    @endif
                </div>

                {{-- Actions --}}
                <ul class="menu-items ab-actions">
                    <li>
                        <a href="{{ route('marble.item.variant.edit', [$item, $variant]) }}" class="clearfix">
                            @include('marble::components.famicon', ['name' => 'pencil']) {{ trans('marble::admin.ab_edit_variant') }}
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('marble.item.variant.delete', [$item, $variant]) }}"
                              onsubmit="return confirm('{{ trans('marble::admin.are_you_sure') }}')" class="marble-inline-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="danger">
                                @include('marble::components.famicon', ['name' => 'bin']) {{ trans('marble::admin.ab_delete_variant') }}
                            </button>
                        </form>
                    </li>
                </ul>
            @endif
        </div>
    </div>
</div>

@if($variant)
<script>
(function () {
    // Set initial widths from data attributes
    document.querySelectorAll('.ab-seg-a, .ab-seg-b').forEach(function (el) {
        el.style.width = el.dataset.pct + '%';
    });

    // Live split preview on drag
    var range = document.querySelector('.ab-split-range-live');
    if (!range) return;
    var section = range.closest('.ab-split-section');
    var segA = section.querySelector('.ab-seg-a');
    var segB = section.querySelector('.ab-seg-b');
    var lblA = section.querySelector('.ab-lbl-a');
    var lblB = section.querySelector('.ab-lbl-b');

    range.addEventListener('input', function () {
        var b = parseInt(this.value);
        var a = 100 - b;
        segA.style.width = a + '%';
        segB.style.width = b + '%';
        lblA.textContent = 'A ' + a + '%';
        lblB.textContent = 'B ' + b + '%';
    });

    // Set stat bar widths from data attributes
    document.querySelectorAll('.ab-stat-bar-fill').forEach(function (el) {
        el.style.width = el.dataset.pct + '%';
    });
})();
</script>
@endif
