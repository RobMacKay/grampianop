@php
  // Variables passed from include:
  // $title, $permalink, $start_date, $end_date, $location, $all_day
  $date_fmt  = ($all_day ?? false) ? 'l j F Y' : 'l j F Y \a\t g:ia';
  $start_str = $start_date ? date($date_fmt, strtotime($start_date)) : null;

  // Prefer ACF event_image, fall back to WP featured image
  $acf_image = get_field('event_image');
  $thumb_url = $acf_image
    ? ($acf_image['sizes']['card_image'] ?? $acf_image['url'])
    : (($id = get_post_thumbnail_id()) ? wp_get_attachment_image_url($id, 'card_image') : null);
@endphp

<div class="w-full bg-white border border-gray-200 rounded-lg shadow-sm not-prose relative">
  <a href="{{ $permalink }}" class="block aspect-video rounded-t overflow-clip border-b border-gray-200">
    @if($thumb_url)
      <img src="{{ $thumb_url }}" alt="{{ $title }}" loading="lazy" class="w-full h-full object-cover object-center" />
    @else
      <div class="w-full h-full bg-og-green-200 flex items-center justify-center">
        <x-heroicon-o-calendar-days class="w-12 h-12 text-og-green-600 opacity-50" />
      </div>
    @endif
  </a>
  <div class="p-5">
    <a href="{{ $permalink }}">
      <h3 class="mb-2 text-xl font-bold tracking-tight text-gray-900 hover:text-blue-700">{{ $title }}</h3>
    </a>
    @if($start_str)
      <p class="text-sm text-gray-500 mb-1">
        <x-heroicon-o-calendar class="w-4 h-4 inline-block mr-1 text-gray-400" />
        {{ $start_str }}
      </p>
    @endif
    @if($location ?? null)
      <p class="text-sm text-gray-500 mb-3">
        <x-heroicon-o-map-pin class="w-4 h-4 inline-block mr-1 text-gray-400" />
        {{ $location }}
      </p>
    @endif
    <a href="{{ $permalink }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 transition-colors">
      Event Details
      <x-heroicon-s-arrow-right class="w-4 h-4 ms-2" />
    </a>
  </div>
</div>
