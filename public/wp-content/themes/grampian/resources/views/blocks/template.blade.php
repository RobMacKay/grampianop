@php
  $template = $block['template'] ?? '';
@endphp

@switch($template)
  @case('contact-form')
    @include('partials.blocks.contact-form')
    @break
  @case('referral-form')
    @include('partials.blocks.referral-form')
    @break
  @case('events-block')
    @include('partials.blocks.events-block')
    @break
  @case('positions-block')
    @include('partials.blocks.positions-block', ['type' => 'job'])
    @break
  @case('volunteer-positions-block')
    @include('partials.blocks.positions-block', ['type' => 'volunteer'])
    @break
  @case('funders-block')
    @include('partials.blocks.funders-block')
    @break
  @case('google-map')
    @include('partials.blocks.google-map')
    @break
@endswitch
