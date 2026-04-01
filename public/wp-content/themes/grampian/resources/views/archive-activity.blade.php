@extends('layouts.app')

@section('content')
  @php
    $activities = new WP_Query([
      'post_type'      => 'activity',
      'posts_per_page' => -1,
      'orderby'        => 'menu_order title',
      'order'          => 'ASC',
    ]);
  @endphp

  <div class="mx-auto max-w-screen-xl px-4 py-10">
    <h1 class="text-3xl font-bold mb-2">Our Activities</h1>
    <p class="text-gray-500 mb-12">What we do at Grampian Opportunities.</p>

    @if($activities->have_posts())
      @php $cards = []; while($activities->have_posts()) { $activities->the_post(); $cards[] = get_the_ID(); } @endphp
      <div id="activities-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-12" x-data="{ open: null }">
        @foreach($cards as $i => $post_id)
          @php setup_postdata($GLOBALS['post'] = get_post($post_id)); @endphp
          @include('components.activity-card', [
            'title'     => get_the_title($post_id),
            'permalink' => get_permalink($post_id),
            'index'     => $i,
          ])
        @endforeach
      </div>
    @else
      <p class="text-gray-500">No activities found.</p>
    @endif
  </div>
  @php wp_reset_postdata(); @endphp
@endsection
