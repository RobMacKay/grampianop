@php
  $icon      = get_field('icon');
  $icon_url  = $icon ? ($icon['sizes']['medium'] ?? $icon['url']) : null;
  $intro     = get_field('intro');
  $cta_text  = get_field('cta_text') ?: 'Find out more';
  $color     = get_field('card_color') ?: 'green';

  $color_map = [
    'red'    => 'from-red-400 to-red-500',
    'orange' => 'from-orange-400 to-orange-500',
    'yellow' => 'from-yellow-400 to-yellow-500',
    'green'  => 'from-og-green-400 to-og-green-500',
    'blue'   => 'from-blue-400 to-blue-500',
    'purple' => 'from-purple-400 to-purple-500',
    'pink'   => 'from-pink-400 to-pink-500',
    'teal'   => 'from-teal-400 to-teal-500',
  ];
  $gradient = $color_map[$color] ?? $color_map['green'];
@endphp

<div
  class="relative flex flex-col items-center bg-linear-to-t {{ $gradient }} rounded-md shadow-md transition lg:hover:-translate-y-1 lg:hover:scale-105 min-h-56 ease-in-out delay-150 lg:hover:z-20 group starting:md:translate-y-10 starting:opacity-0 starting:duration-600 transition-all"
  x-bind:class="open === {{ $index }} ? 'order-first lg:order-none col-span-full lg:col-span-1 row-span-2 lg:row-span-1' : 'order-none col-span-1 row-span-1'"
>
  <a href="{{ $permalink }}" class="flex relative w-full flex-1 justify-center" :class="open === {{ $index }} ? 'h-24' : ''">
    @if($icon_url)
      <img
        src="{{ $icon_url }}"
        alt="{{ $title }}"
        width="250"
        height="250"
        class="object-cover overflow-clip rounded-full bg-white p-2 lg:p-3 lg:w-1/2 shadow-lg absolute -top-3 md:-top-5"
        x-bind:class="open === {{ $index }} ? 'max-w-[60%] lg:max-w-[200px]' : 'max-w-[120px] lg:max-w-[200px]'"
      />
    @else
      <div
        class="rounded-full bg-white/40 flex items-center justify-center shadow-lg absolute -top-3 md:-top-5 p-2 lg:p-3 lg:w-1/2"
        x-bind:class="open === {{ $index }} ? 'max-w-[60%] lg:max-w-[200px] w-[150px] h-[150px]' : 'max-w-[120px] lg:max-w-[200px] w-[120px] h-[120px]'"
      >
        <x-heroicon-o-users class="w-12 h-12 text-white opacity-80" />
      </div>
    @endif
  </a>

  <div
    class="bg-white rounded-md border border-gray-200 w-full overflow-hidden px-4 lg:group-hover:h-fit lg:group-hover:min-h-[250px] lg:group-hover:absolute lg:group-hover:-bottom-1/2"
    :class="open === {{ $index }} ? 'lg:h-fit lg:min-h-[250px] lg:-bottom-1/2 lg:absolute' : 'h-1/2'"
    @click="open = {{ $index }}, document.getElementById('activities-cards').scrollIntoView()"
  >
    <div
      class="h-fit w-full leading-normal font-semibold text-center text-pretty transition-all lg:group-hover:line-clamp-none"
      :class="open === {{ $index }} ? 'line-clamp-none min-h-[200px] pt-8 md:pt-4' : 'line-clamp-3 pt-4'"
    >
      {{ $title }}
      @if($intro)
        <p class="font-normal text-sm text-gray-600 mt-2">{!! nl2br(esc_html($intro)) !!}</p>
      @endif
      <a
        href="{{ $permalink }}"
        class="w-full text-center py-1 border-2 border-og-green-400 bg-linear-to-b from-og-green-200 to-og-green-400/40 rounded-md text-black my-5 lg:group-hover:block"
        x-bind:class="open === {{ $index }} ? 'block' : 'hidden'"
        x-cloak
      >{{ $cta_text }}</a>
    </div>
  </div>
</div>
