<form role="search" method="get" action="{{ home_url('/') }}" class="flex gap-0">
  <label for="search-form-input" class="sr-only">
    {{ _x('Search for:', 'label', 'sage') }}
  </label>
  <input
    id="search-form-input"
    type="search"
    name="s"
    value="{{ get_search_query() }}"
    placeholder="{{ esc_attr_x('Search…', 'placeholder', 'sage') }}"
    class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-og-green-400 focus:border-transparent"
    required
  />
  <button
    type="submit"
    class="inline-flex items-center gap-1.5 bg-og-green-400 hover:bg-og-green-600 text-white font-medium text-sm px-4 py-2 rounded-r-lg transition-colors"
  >
    <x-heroicon-m-magnifying-glass class="w-4 h-4" />
    {{ _x('Search', 'submit button', 'sage') }}
  </button>
</form>
