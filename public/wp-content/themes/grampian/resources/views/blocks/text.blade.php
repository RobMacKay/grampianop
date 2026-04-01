@php
  $bg_map = [
    'primary' => 'bg-og-green-200',
    'light'   => 'bg-gray-50',
    'default' => 'bg-white',
  ];
  $bg_class = $bg_map[$block['background_color'] ?? 'default'] ?? 'bg-white';
@endphp

<section class="py-10 {{ $bg_class }}">
  <div class="mx-auto max-w-screen-xl px-4">
    <div class="w-full md:w-3/4 prose max-w-none">
      @if(!empty($block['title']))
        <h2>{{ $block['title'] }}</h2>
      @endif
      {!! $block['content'] !!}
    </div>
  </div>
</section>
