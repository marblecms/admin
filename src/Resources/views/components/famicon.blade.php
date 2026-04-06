@php
static $cachedTheme = null;
if ($cachedTheme === null) {
    $cachedTheme = Auth::guard('marble')->user()?->theme ?? 'xp';
}
@endphp
<img src="{{ \Marble\Admin\Support\Win98Icons::url($name, $cachedTheme) }}" width="16" height="16" alt="" class="marble-famicon">
