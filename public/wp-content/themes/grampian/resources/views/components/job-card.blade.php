@php
  // Variables: $title, $permalink, $type, $hours, $salary, $location, $closing_date
  $type_label = match($type ?? 'job') {
      'volunteer' => 'Volunteer',
      default     => 'Paid Position',
  };
  $type_colour = ($type ?? 'job') === 'volunteer'
      ? 'bg-red-100 text-red-700'
      : 'bg-blue-100 text-blue-700';

  $closing_str = ($closing_date ?? null) ? date('j F Y', strtotime($closing_date)) : null;
@endphp

<a href="{{ $permalink }}" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 not-prose transition-colors">
  <div class="flex items-start justify-between gap-2 mb-3">
    <h3 class="text-xl font-bold tracking-tight text-gray-900">{{ $title }}</h3>
    <span class="shrink-0 inline-block px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $type_colour }}">
      {{ $type_label }}
    </span>
  </div>

  <dl class="space-y-1 text-sm text-gray-600">
    @if($location ?? null)
      <div class="flex items-center gap-1.5">
        <x-heroicon-o-map-pin class="w-4 h-4 text-gray-400 shrink-0" />
        <dd>{{ $location }}</dd>
      </div>
    @endif
    @if($hours ?? null)
      <div class="flex items-center gap-1.5">
        <x-heroicon-o-clock class="w-4 h-4 text-gray-400 shrink-0" />
        <dd>{{ $hours }}</dd>
      </div>
    @endif
    @if($salary ?? null)
      <div class="flex items-center gap-1.5">
        <x-heroicon-o-banknotes class="w-4 h-4 text-gray-400 shrink-0" />
        <dd>{{ $salary }}</dd>
      </div>
    @endif
    @if($closing_str)
      <div class="flex items-center gap-1.5">
        <x-heroicon-o-calendar class="w-4 h-4 text-gray-400 shrink-0" />
        <dd>Closing: {{ $closing_str }}</dd>
      </div>
    @endif
  </dl>

  <span class="mt-4 inline-flex items-center text-sm font-medium text-blue-700 hover:underline">
    View Position <x-heroicon-s-arrow-right class="w-4 h-4 ms-1" />
  </span>
</a>
