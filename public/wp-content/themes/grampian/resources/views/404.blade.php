@extends('layouts.app')

@section('content')
  <div class="mx-auto max-w-screen-xl px-4 py-20 text-center">
    <p class="text-8xl font-black text-og-green-400 mb-4">404</p>
    <h1 class="text-3xl font-bold text-gray-900 mb-4">
      {{ __('Page not found', 'sage') }}
    </h1>
    <p class="text-gray-500 mb-8 max-w-md mx-auto">
      {{ __("Sorry, the page you're looking for doesn't exist or may have moved.", 'sage') }}
    </p>
    <div class="flex flex-wrap justify-center gap-4">
      <a href="{{ home_url('/') }}" class="btn btn--primary">
        {{ __('Back to Home', 'sage') }}
      </a>
      <a href="{{ home_url('/contact') }}" class="btn btn--secondary">
        {{ __('Contact Us', 'sage') }}
      </a>
    </div>
    <div class="mt-10 max-w-sm mx-auto">
      @include('forms.search')
    </div>
  </div>
@endsection
