@extends('layouts.app')

@section('content')
  @php
    // Split by Event Type (see EventRecurrence::isRecurring); events that have
    // finished are left out of both lists.
    ['recurring' => $recurring, 'one_off' => $upcoming] = \App\Content::eventGroups();
  @endphp

  <div class="mx-auto max-w-screen-xl px-4 py-10">
    <h1 class="text-3xl font-bold mb-2">What's On</h1>
    <p class="text-gray-500 mb-8">Upcoming events and activities from Grampian Opportunities.</p>

    @if($recurring)
      <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Regular Events</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          @foreach($recurring as $event_id)
            @php setup_postdata($GLOBALS['post'] = get_post($event_id)); @endphp
            @include('components.event-card-compact', [
              'title'     => get_the_title(),
              'permalink' => get_permalink(),
              'location'  => get_field('location'),
              'rec_note'  => \App\EventRecurrence::scheduleLabel($event_id),
            ])
          @endforeach
        </div>
      </section>
      @php wp_reset_postdata(); @endphp
    @endif

    @if($upcoming)
      <section>
        <h2 class="text-xl font-bold text-gray-800 mb-4">Upcoming Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($upcoming as $event_id)
            @php setup_postdata($GLOBALS['post'] = get_post($event_id)); @endphp
            @include('components.event-card', [
              'title'      => get_the_title(),
              'permalink'  => get_permalink(),
              'start_date' => get_field('start_date'),
              'end_date'   => get_field('end_date'),
              'location'   => get_field('location'),
              'all_day'    => get_field('all_day'),
            ])
          @endforeach
        </div>
      </section>
      @php wp_reset_postdata(); @endphp
    @endif

    @if(!$recurring && !$upcoming)
      <p class="text-gray-500">No upcoming events at the moment. Please check back soon!</p>
    @endif
  </div>
@endsection
