@php
  // $type is passed from the template block: 'job', 'volunteer', or null for all
  $type = $type ?? null;

  $meta_query = [['relation' => 'AND']];

  if ($type === 'volunteer') {
    $meta_query[] = [
      'key'     => 'position_type',
      'value'   => 'volunteer',
      'compare' => '=',
    ];
  } elseif ($type === 'job') {
    $meta_query[] = [
      'key'     => 'position_type',
      'value'   => 'volunteer',
      'compare' => '!=',
    ];
  }

  // Exclude expired positions
  $meta_query[] = [
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
  ];

  $positions = new WP_Query([
    'post_type'      => 'position',
    'posts_per_page' => 20,
    'meta_query'     => $meta_query,
    'orderby'        => 'date',
    'order'          => 'DESC',
  ]);

  $heading = $type === 'volunteer' ? 'Volunteering Opportunities' : ($type === 'job' ? 'Current Positions' : 'Positions & Opportunities');
@endphp

<section class="py-10">
  <div class="mx-auto max-w-screen-xl px-4">
    <h2 class="text-2xl font-semibold mb-6">{{ $heading }}</h2>
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
      <p class="text-gray-500">
        {{ $type === 'volunteer' ? 'No current volunteering opportunities.' : 'No current positions.' }}
        Please <a href="{{ home_url('/contact') }}" class="text-blue-700 hover:underline">get in touch</a> if you are interested.
      </p>
    @endif
  </div>
</section>
@php wp_reset_postdata(); @endphp
