{{--
  Template Name: Flexible Content Page
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php the_post(); @endphp

    {{-- Flexible content blocks --}}
    @php $blocks = get_field('content_blocks'); @endphp
    @if($blocks)
      @foreach($blocks as $block)
        @php $layout = $block['acf_fc_layout']; @endphp
        @switch($layout)
          @case('hero')
            @include('blocks.hero', ['block' => $block])
            @break
          @case('text')
            @include('blocks.text', ['block' => $block])
            @break
          @case('text_image')
            @include('blocks.text-image', ['block' => $block])
            @break
          @case('image')
            @include('blocks.image', ['block' => $block])
            @break
          @case('video')
            @include('blocks.video', ['block' => $block])
            @break
          @case('html')
            @include('blocks.html', ['block' => $block])
            @break
          @case('overview')
            @include('blocks.overview', ['block' => $block])
            @break
          @case('quote')
            @include('blocks.quote', ['block' => $block])
            @break
          @case('call_to_action')
            @include('blocks.call-to-action', ['block' => $block])
            @break
          @case('cards')
            @include('blocks.cards', ['block' => $block])
            @break
          @case('template')
            @include('blocks.template', ['block' => $block])
            @break
          @case('activities')
            @include('blocks.activities', ['block' => $block])
            @break
          @case('go_hero')
            @include('blocks.go-hero', ['block' => $block])
            @break
          @case('go_audience_router')
            @include('blocks.go-audience-router', ['block' => $block])
            @break
          @case('go_service_grid')
            @include('blocks.go-service-grid', ['block' => $block])
            @break
          @case('go_impact_band')
            @include('blocks.go-impact-band', ['block' => $block])
            @break
          @case('go_events_strip')
            @include('blocks.go-events-strip', ['block' => $block])
            @break
          @case('go_get_involved')
            @include('blocks.go-get-involved', ['block' => $block])
            @break
          @case('go_logo_wall')
            @include('blocks.go-logo-wall', ['block' => $block])
            @break
          @case('go_visit')
            @include('blocks.go-visit', ['block' => $block])
            @break
        @endswitch
      @endforeach
    @endif

  @endwhile
@endsection
