@extends('layouts.app')

@section('content')
  @while(have_posts())
    @php the_post() @endphp
    @includeFirst(['partials.content-single-activity', 'partials.content-single'])

    @if(\App\Forms\BookingForm::isBookable(get_the_ID()) || \App\Forms\BookingForm::submitted())
      @include('partials.booking-form', [
        'item_id'    => get_the_ID(),
        'form_id'    => 'booking-form-' . get_the_ID(),
        'is_preview' => false,
      ])
    @endif
  @endwhile
@endsection
