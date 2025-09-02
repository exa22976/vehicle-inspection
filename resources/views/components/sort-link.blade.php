@props([
'label', // 表示ラベル
'column', // データベースのカラム名
'sortColumn' => request('sort', 'id'), // 現在ソート中のカラム
'sortDirection' => request('direction', 'asc') // 現在のソート方向
])

@php
// クリックされた際の新しいソート方向を決定
$newDirection = ($sortColumn == $column && $sortDirection == 'asc') ? 'desc' : 'asc';
@endphp

<a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $newDirection]) }}" class="flex items-center">
    {{ $label }}
    @if ($sortColumn == $column)
    @if ($sortDirection == 'asc')
    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
    </svg>
    @else
    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
    </svg>
    @endif
    @endif
</a>