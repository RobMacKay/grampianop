{{--
  Template Name: Contact

  Renders the Contact Form block, so this page and the block share one form,
  one design and one submissions pipeline.
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php the_post() @endphp

    @include('blocks.contact-form', [
      'block' => [
        'heading'       => get_the_title(),
        'heading_level' => 'h1',
        'intro'         => apply_filters('the_content', get_the_content()),
      ],
      'is_preview' => false,
      'block_id'   => 'contact-page',
    ])
  @endwhile
@endsection
