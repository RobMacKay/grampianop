@extends('layouts.app')

@section('content')
  @php
    $positions = new WP_Query([
      'post_type'      => 'position',
      'posts_per_page' => -1,
      'meta_query'     => [
        'relation' => 'OR',
        [
          'key'     => 'closing_date',
          'value'   => date('Y-m-d'),
          'compare' => '>=',
          'type'    => 'DATE',
        ],
        [
          'key'     => 'closing_date',
          'compare' => 'NOT EXISTS',
        ],
        [
          'key'   => 'closing_date',
          'value' => '',
        ],
      ],
      'orderby' => 'date',
      'order'   => 'DESC',
    ]);
  @endphp

  <div class="mx-auto max-w-screen-xl px-4 py-10">
    <h1 class="text-3xl font-bold mb-2">Current Positions &amp; Vacancies</h1>
    <p class="text-gray-500 mb-8">Jobs and volunteering opportunities with Grampian Opportunities.</p>

    @if($positions->have_posts())
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @while($positions->have_posts())
          @php $positions->the_post(); @endphp
          @include('components.job-card', [
            'title'        => get_the_title(),
            'permalink'    => get_permalink(),
            'type'         => get_field('position_type'),
            'hours'        => get_field('hours'),
            'salary'       => get_field('salary'),
            'location'     => get_field('location'),
            'closing_date' => get_field('closing_date'),
          ])
        @endwhile
      </div>
    @else
      <p class="text-gray-500">No current positions. Please check back soon, or <a href="{{ home_url('/contact') }}" class="text-blue-700 hover:underline">get in touch</a> to register your interest.</p>
    @endif
  </div>
  @php wp_reset_postdata(); @endphp
@endsection
