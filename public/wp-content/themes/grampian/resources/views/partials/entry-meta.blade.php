<div class="flex items-center gap-4 text-sm text-gray-500 mt-1 mb-4">
  <time class="flex items-center gap-1" datetime="{{ get_post_time('c', true) }}">
    <x-heroicon-o-calendar class="w-4 h-4" />
    {{ get_the_date() }}
  </time>
  <span class="flex items-center gap-1">
    <x-heroicon-o-user class="w-4 h-4" />
    <a href="{{ get_author_posts_url(get_the_author_meta('ID')) }}" class="hover:text-blue-700 hover:underline">
      {{ get_the_author() }}
    </a>
  </span>
  @if (has_category())
    <span class="flex items-center gap-1">
      <x-heroicon-o-tag class="w-4 h-4" />
      {!! get_the_category_list(', ') !!}
    </span>
  @endif
</div>
