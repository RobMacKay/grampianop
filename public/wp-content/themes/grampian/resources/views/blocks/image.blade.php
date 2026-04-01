@php
  $image = $block['image'] ?? null;
  $width_map = [
    'full'         => 'w-full',
    'three-quarter' => 'w-full md:w-3/4 mx-auto',
    'half'         => 'w-full md:w-1/2 mx-auto',
    'third'        => 'w-full md:w-1/3 mx-auto',
  ];
  $width_class = $width_map[$block['width'] ?? 'full'] ?? 'w-full';
@endphp

@if($image)
  <section class="py-6">
    <div class="mx-auto max-w-screen-xl px-4">
      <figure class="{{ $width_class }}">
        <img
          src="{{ $image['url'] }}"
          alt="{{ $image['alt'] }}"
          width="{{ $image['width'] }}"
          height="{{ $image['height'] }}"
          loading="lazy"
          class="w-full rounded-lg"
        />
        @if(!empty($block['copyright']))
          <figcaption class="text-sm text-gray-500 mt-1">&copy; {{ $block['copyright'] }}</figcaption>
        @endif
      </figure>
    </div>
  </section>
@endif
