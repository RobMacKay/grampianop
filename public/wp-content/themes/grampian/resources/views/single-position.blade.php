@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php the_post(); @endphp
    @php
      $type         = get_field('position_type');
      $description  = get_field('description');
      $closing_date = get_field('closing_date');
      $hours        = get_field('hours');
      $salary       = get_field('salary');
      $location     = get_field('location');
      $apply_url    = get_field('apply_url');
      $document     = get_field('document');

      $type_label = match($type) {
          'volunteer' => 'Volunteer',
          default     => 'Paid Position',
      };

      $closing_str = $closing_date ? date('j F Y', strtotime($closing_date)) : null;
    @endphp

    <article class="mx-auto max-w-screen-xl px-4 py-10">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        {{-- Main content --}}
        <div class="md:col-span-2">
          <div class="flex items-center gap-3 mb-2">
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full {{ $type === 'volunteer' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
              {{ $type_label }}
            </span>
          </div>
          <h1 class="text-3xl font-bold mb-6">{{ get_the_title() }}</h1>

          @if($description)
            <div class="prose max-w-none">
              {!! $description !!}
            </div>
          @endif
        </div>

        {{-- Sidebar --}}
        <aside class="bg-gray-50 border border-gray-100 rounded-lg p-6 h-fit space-y-4">
          @if($location)
            <div>
              <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Location</dt>
              <dd class="mt-1 font-medium text-gray-900">{{ $location }}</dd>
            </div>
          @endif
          @if($hours)
            <div>
              <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Hours</dt>
              <dd class="mt-1 font-medium text-gray-900">{{ $hours }}</dd>
            </div>
          @endif
          @if($salary)
            <div>
              <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Salary</dt>
              <dd class="mt-1 font-medium text-gray-900">{{ $salary }}</dd>
            </div>
          @endif
          @if($closing_str)
            <div>
              <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Closing Date</dt>
              <dd class="mt-1 font-medium text-gray-900">{{ $closing_str }}</dd>
            </div>
          @endif

          @if($document)
            <a href="{{ $document['url'] }}" target="_blank" rel="noopener" class="btn btn--secondary w-full text-center block">
              Download Job Description
            </a>
          @endif

          @if($apply_url)
            <a href="{{ $apply_url }}" target="_blank" rel="noopener" class="btn btn--primary w-full text-center block">
              Apply Now
            </a>
          @else
            <a href="{{ home_url('/contact') }}" class="btn btn--primary w-full text-center block">
              Express Interest
            </a>
          @endif

          <a href="{{ get_post_type_archive_link('position') }}" class="block text-sm text-blue-700 hover:underline text-center">
            &larr; All Positions
          </a>
        </aside>
      </div>
    </article>
  @endwhile
@endsection
