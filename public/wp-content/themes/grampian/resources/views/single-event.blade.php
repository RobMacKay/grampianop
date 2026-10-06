@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php the_post(); @endphp
    @php
      $start_date  = get_field('start_date');
      $end_date    = get_field('end_date');
      $location    = get_field('location');
      $description = get_field('description');
      $booking_url = get_field('booking_url');
      $all_day     = get_field('all_day');
      $recurring        = \App\EventRecurrence::isRecurring(get_the_ID());
      $schedule         = $recurring ? \App\EventRecurrence::scheduleLabel(get_the_ID()) : '';
      $rec_end          = $recurring ? get_field('recurrence_end_date') : null;
      $event_img        = get_field('event_image');
      $bookable         = \App\Forms\BookingForm::isBookable(get_the_ID());
      $is_full          = $bookable && \App\Forms\BookingSpaces::isFull(get_the_ID());

      $rec_end_str = $rec_end ? date('j F Y', strtotime($rec_end)) : null;

      $date_fmt  = $all_day ? 'l j F Y' : 'l j F Y \a\t g:ia';
      // A recurring event can still carry dates saved before it was switched over,
      // so only a one-off event shows a date.
      $start_str = (! $recurring && $start_date) ? date($date_fmt, strtotime($start_date)) : null;
      $end_str   = (! $recurring && $end_date)   ? date($date_fmt, strtotime($end_date))   : null;
    @endphp

    <article class="mx-auto max-w-screen-xl px-4 py-10">
      {{-- Hero image --}}
      @if($event_img)
        <div class="mb-6 rounded-lg overflow-hidden">
          <img
            src="{{ $event_img['sizes']['hero_image'] ?? $event_img['url'] }}"
            alt="{{ $event_img['alt'] ?: get_the_title() }}"
            loading="eager"
            class="w-full object-cover max-h-72"
          />
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        {{-- Main content --}}
        <div class="md:col-span-2">
          <h1 class="text-3xl font-bold mb-4">{{ get_the_title() }}</h1>
          @if($description)
            <div class="prose max-w-none">
              {!! $description !!}
            </div>
          @endif
        </div>

        {{-- Sidebar details --}}
        <aside class="bg-gray-50 border border-gray-100 rounded-lg p-6 h-fit space-y-4">
          @if($start_str)
            <div>
              <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</dt>
              <dd class="mt-1 font-medium text-gray-900">
                {{ $start_str }}
                @if($end_str && $end_str !== $start_str)
                  <br /><span class="text-sm text-gray-500">to {{ $end_str }}</span>
                @endif
              </dd>
            </div>
          @endif

          @if($recurring)
            <div>
              <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Repeats</dt>
              <dd class="mt-1 font-medium text-gray-900">
                {{ $schedule }}
                @if($rec_end_str)
                  <span class="block text-sm text-gray-500">Until {{ $rec_end_str }}</span>
                @endif
              </dd>
            </div>
          @endif

          @if($location)
            <div>
              <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Location</dt>
              <dd class="mt-1 font-medium text-gray-900">{{ $location }}</dd>
            </div>
          @endif

          @if($bookable)
            <a href="#book" class="block w-full text-center px-[26px] py-4 rounded-full bg-go-green-deep text-white font-heading font-bold text-[17px] leading-none hover:bg-go-green-deepest transition-colors">
              {{ $is_full ? 'Fully booked' : 'Book a place' }}
            </a>
          @elseif($booking_url)
            <a href="{{ $booking_url }}" target="_blank" rel="noopener" class="btn btn--primary w-full text-center block">
              Book / More Info
            </a>
          @endif

          <a href="{{ get_post_type_archive_link('event') }}" class="block text-sm text-blue-700 hover:underline text-center">
            &larr; All Events
          </a>
        </aside>
      </div>
    </article>

    @if($bookable || \App\Forms\BookingForm::submitted())
      @include('partials.booking-form', [
        'item_id'    => get_the_ID(),
        'form_id'    => 'booking-form-' . get_the_ID(),
        'is_preview' => false,
      ])
    @endif
  @endwhile
@endsection
