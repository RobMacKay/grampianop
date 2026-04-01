@php
  $thumb_id  = get_post_thumbnail_id();
  $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'card_image') : null;
@endphp

<article @php(post_class('bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col'))>
  <a href="{{ get_permalink() }}" class="block aspect-video border-b border-gray-200 overflow-hidden">
    @if ($thumb_url)
      <img src="{{ $thumb_url }}" alt="{{ get_the_title() }}" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" />
    @else
      <div class="w-full h-full bg-og-green-200 flex items-center justify-center">
        <x-heroicon-o-document-text class="w-12 h-12 text-og-green-600 opacity-50" />
      </div>
    @endif
  </a>

  <div class="p-5 flex flex-col flex-1">
    @include('partials.entry-meta')

    <h2 class="text-xl font-bold text-gray-900 mb-2">
      <a href="{{ get_permalink() }}" class="hover:text-blue-700">
        {!! $title !!}
      </a>
    </h2>

    <div class="text-gray-600 text-sm flex-1 mb-4">
      @php(the_excerpt())
    </div>

    <a href="{{ get_permalink() }}" class="inline-flex items-center text-sm font-medium text-blue-700 hover:underline">
      {{ __('Read more', 'sage') }}
      <x-heroicon-s-arrow-right class="w-4 h-4 ms-1" />
    </a>
  </div>
</article>
