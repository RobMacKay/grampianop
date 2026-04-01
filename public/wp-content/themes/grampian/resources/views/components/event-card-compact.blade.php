@php
  $acf_image = get_field('event_image');
  $thumb_url = $acf_image
    ? ($acf_image['sizes']['thumbnail'] ?? $acf_image['url'])
    : (($id = get_post_thumbnail_id()) ? wp_get_attachment_image_url($id, 'thumbnail') : null);
@endphp

<a href="{{ $permalink }}" class="flex items-stretch bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md hover:border-gray-300 transition-all overflow-hidden not-prose">
  <div class="flex-1 p-4 min-w-0">
    <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-1 truncate">{{ $title }}</h3>
    @if($rec_note ?? null)
      <p class="text-xs text-gray-500 mb-1">{{ $rec_note }}</p>
    @elseif(($rec_days ?? []) || ($rec_time ?? null))
      <p class="text-xs text-gray-500 mb-1">
        @if($rec_days ?? []){{ implode(', ', array_map('ucfirst', $rec_days)) }}@endif
        @if($rec_time ?? null) &middot; {{ $rec_time }}@endif
      </p>
    @elseif($rec_type ?? null)
      <p class="text-xs text-gray-500 mb-1 capitalize">{{ $rec_type }}</p>
    @endif
    @if($location ?? null)
      <p class="text-xs text-gray-400 flex items-center gap-0.5">
        <x-heroicon-o-map-pin class="w-3 h-3 shrink-0" />
        <span class="truncate">{{ $location }}</span>
      </p>
    @endif
  </div>
  <div class="w-20 shrink-0">
    @if($thumb_url)
      <img src="{{ $thumb_url }}" alt="{{ $title }}" loading="lazy" class="w-full h-full object-cover" />
    @else
      <div class="w-full h-full bg-og-green-200 flex items-center justify-center">
        <x-heroicon-o-calendar-days class="w-6 h-6 text-og-green-600 opacity-50" />
      </div>
    @endif
  </div>
</a>
