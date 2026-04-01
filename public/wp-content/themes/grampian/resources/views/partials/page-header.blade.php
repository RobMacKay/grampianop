<div class="bg-gradient-to-b from-gray-50 to-white border-b border-gray-100 py-8">
  <div class="mx-auto max-w-screen-xl px-4">
    <h1 class="text-3xl font-bold text-gray-900">
      @if (is_search())
        {{ __('Search Results for:', 'sage') }} <span class="text-blue-700">&ldquo;{{ get_search_query() }}&rdquo;</span>
      @elseif (is_404())
        {{ __('Page Not Found', 'sage') }}
      @elseif (is_archive())
        {!! get_the_archive_title() !!}
      @else
        {!! $title !!}
      @endif
    </h1>
    @if (is_archive() && get_the_archive_description())
      <p class="mt-2 text-gray-500">{!! get_the_archive_description() !!}</p>
    @endif
  </div>
</div>
