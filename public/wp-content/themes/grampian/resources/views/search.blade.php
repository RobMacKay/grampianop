@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  <div class="mx-auto max-w-screen-xl px-4 py-10">

    @if (! have_posts())
      <div class="text-center py-16">
        <x-heroicon-o-magnifying-glass class="w-12 h-12 mx-auto text-gray-300 mb-4" />
        <p class="text-xl font-semibold text-gray-700 mb-2">{{ __('No results found', 'sage') }}</p>
        <p class="text-gray-500 mb-6">{{ __('Try a different search term or browse the site.', 'sage') }}</p>
        @include('forms.search')
      </div>
    @else
      @php
        // Group results by post type
        $groups = [];
        while (have_posts()) {
          the_post();
          $type = get_post_type();
          $groups[$type][] = get_post();
        }
        rewind_posts();

        $type_labels = [
          'page'     => 'Pages',
          'post'     => 'News &amp; Updates',
          'event'    => 'Events',
          'position' => 'Positions &amp; Vacancies',
        ];
      @endphp

      <div class="grid grid-cols-1 md:grid-cols-{{ count($groups) > 1 ? '3' : '1' }} gap-8 mb-8">
        @foreach($groups as $type => $posts)
          <div>
            <h2 class="text-xl font-bold border-b-2 border-og-green-400 pb-2 mb-4">
              {!! $type_labels[$type] ?? ucfirst($type) !!}
              <span class="text-sm font-normal text-gray-500 ml-1">({{ count($posts) }})</span>
            </h2>
            @foreach($posts as $post)
              @php setup_postdata($post); @endphp
              @include('partials.content-search')
            @endforeach
          </div>
        @endforeach
      </div>
      @php wp_reset_postdata(); @endphp

      @php
        $nav = get_the_posts_pagination([
          'mid_size'  => 2,
          'prev_text' => '&larr; ' . __('Previous', 'sage'),
          'next_text' => __('Next', 'sage') . ' &rarr;',
        ]);
      @endphp
      @if ($nav)
        <nav class="mt-6 flex justify-center">{!! $nav !!}</nav>
      @endif
    @endif

  </div>
@endsection
