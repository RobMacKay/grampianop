@props([
  'type'    => 'info',
  'message' => null,
])

@php
  $styles = match ($type) {
    'success' => 'bg-green-50 border-green-400 text-green-800',
    'warning' => 'bg-red-50 border-red-400 text-red-800',
    'caution' => 'bg-yellow-50 border-yellow-400 text-yellow-800',
    default   => 'bg-blue-50 border-blue-400 text-blue-800',
  };
  $icon = match ($type) {
    'success' => 'heroicon-o-check-circle',
    'warning' => 'heroicon-o-exclamation-triangle',
    'caution' => 'heroicon-o-exclamation-circle',
    default   => 'heroicon-o-information-circle',
  };
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 px-4 py-3 border-l-4 rounded-r-lg {$styles}"]) }}>
  <x-dynamic-component :component="$icon" class="w-5 h-5 shrink-0 mt-0.5" />
  <div class="text-sm">
    {!! $message ?? $slot !!}
  </div>
</div>
