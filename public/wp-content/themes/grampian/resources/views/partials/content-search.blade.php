@php
  $type       = get_post_type();
  $type_label = match($type) {
    'event'    => 'Event',
    'position' => 'Position',
    'page'     => 'Page',
    default    => 'Post',
  };
  $type_colour = match($type) {
    'event'    => 'bg-green-100 text-green-700',
    'position' => 'bg-blue-100 text-blue-700',
    'page'     => 'bg-gray-100 text-gray-700',
    default    => 'bg-indigo-100 text-indigo-700',
  };
@endphp

<article class="flex gap-4 py-4 border-b border-gray-100 last:border-0">
  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-2 mb-1">
      <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full {{ $type_colour }}">{{ $type_label }}</span>
      @if ($type === 'post')
        <time class="text-xs text-gray-400">{{ get_the_date() }}</time>
      @endif
    </div>
    <h2 class="text-lg font-semibold text-gray-900">
      <a href="{{ get_permalink() }}" class="hover:text-blue-700">
        {!! $title !!}
      </a>
    </h2>
    <p class="text-sm text-gray-600 mt-1 line-clamp-2">
      {{ wp_strip_all_tags(get_the_excerpt()) }}
    </p>
  </div>
  <div class="shrink-0 self-center">
    <a href="{{ get_permalink() }}" class="text-sm font-medium text-blue-700 hover:underline whitespace-nowrap">
      View &rarr;
    </a>
  </div>
</article>
