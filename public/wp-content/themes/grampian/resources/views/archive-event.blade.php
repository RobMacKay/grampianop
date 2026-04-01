@extends('layouts.app')

@section('content')
  @php
    $recurring = new WP_Query([
      'post_type'      => 'event',
      'posts_per_page' => -1,
      'orderby'        => 'title',
      'order'          => 'ASC',
      'meta_query'     => [
        ['key' => 'recurring', 'value' => '1', 'compare' => '='],
      ],
    ]);

    $upcoming = new WP_Query([
      'post_type'      => 'event',
      'posts_per_page' => -1,
      'meta_key'       => 'start_date',
      'meta_value'     => date('Y-m-d H:i:s'),
      'meta_compare'   => '>=',
      'orderby'        => 'meta_value',
      'order'          => 'ASC',
      'meta_query'     => [
        'relation' => 'OR',
        ['key' => 'recurring', 'value' => '1', 'compare' => '!='],
        ['key' => 'recurring', 'compare' => 'NOT EXISTS'],
      ],
    ]);
  @endphp

  <div class="mx-auto max-w-screen-xl px-4 py-10">
    <h1 class="text-3xl font-bold mb-2">What's On</h1>
    <p class="text-gray-500 mb-8">Upcoming events and activities from Grampian Opportunities.</p>

    @if($recurring->have_posts())
      <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Regular Events</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          @while($recurring->have_posts())
            @php $recurring->the_post(); @endphp
            @include('components.event-card-compact', [
              'title'     => get_the_title(),
              'permalink' => get_permalink(),
              'location'  => get_field('location'),
              'rec_note'  => get_field('recurring_note'),
              'rec_days'  => get_field('recurrence_days') ?: [],
              'rec_time'  => get_field('recurrence_time'),
              'rec_type'  => get_field('recurrence_type'),
            ])
          @endwhile
        </div>
      </section>
      @php wp_reset_postdata(); @endphp
    @endif

    @if($upcoming->have_posts())
      <section>
        <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @while($upcoming->have_posts())
            @php $upcoming->the_post(); @endphp
            @include('components.event-card', [
              'title'      => get_the_title(),
              'permalink'  => get_permalink(),
              'start_date' => get_field('start_date'),
              'end_date'   => get_field('end_date'),
              'location'   => get_field('location'),
              'all_day'    => get_field('all_day'),
            ])
          @endwhile
        </div>
      </section>
      @php wp_reset_postdata(); @endphp
    @endif

    @if(!$recurring->have_posts() && !$upcoming->have_posts())
      <p class="text-gray-500">No upcoming events at the moment. Please check back soon!</p>
    @endif
  </div>
@endsection
