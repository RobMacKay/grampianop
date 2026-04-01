@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  <div class="mx-auto max-w-screen-xl px-4 py-10">
    @if (! have_posts())
      <x-alert type="warning">
        {!! __('Sorry, no posts were found.', 'sage') !!}
      </x-alert>
      @include('forms.search')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @while(have_posts())
        @php the_post() @endphp
        @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
      @endwhile
    </div>

    @php
      $nav = get_the_posts_pagination([
        'mid_size'  => 2,
        'prev_text' => '&larr; ' . __('Newer', 'sage'),
        'next_text' => __('Older', 'sage') . ' &rarr;',
      ]);
    @endphp
    @if ($nav)
      <nav class="mt-10 flex justify-center" aria-label="{{ __('Posts navigation', 'sage') }}">
        {!! $nav !!}
      </nav>
    @endif
  </div>
@endsection
