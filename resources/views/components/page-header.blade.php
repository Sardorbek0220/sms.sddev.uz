{{--
    Page header — title, optional subtitle, breadcrumbs, action slot.

    Laravel 7 anonymous components don't have @props, so non-string props (like the
    breadcrumbs array) leak into $attributes and break ->merge(). We strip them first.
--}}
@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? null;
    $breadcrumbs = isset($breadcrumbs) && is_array($breadcrumbs) ? $breadcrumbs : [];
    // Strip declared props from attribute bag so merge() never tries to stringify them.
    $attributes = $attributes->except(['title', 'subtitle', 'breadcrumbs']);
@endphp

<header {{ $attributes->merge(['class' => 'ph-page-header']) }}>
    <div>
        @if(!empty($breadcrumbs))
            <nav class="ph-breadcrumbs" aria-label="breadcrumb">
                @foreach($breadcrumbs as $i => $crumb)
                    @if(!empty($crumb['url']) && $i !== count($breadcrumbs) - 1)
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] ?? '' }}</a>
                    @else
                        <span>{{ $crumb['label'] ?? '' }}</span>
                    @endif
                    @if($i !== count($breadcrumbs) - 1)
                        <span class="px-1 text-muted">/</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1>{{ $title }}</h1>
        @if($subtitle)
            <div class="text-muted small mt-1">{{ $subtitle }}</div>
        @endif
    </div>

    @isset($actions)
        <div class="ph-page-header-actions d-flex flex-wrap" style="gap:8px;">
            {{ $actions }}
        </div>
    @endisset
</header>
