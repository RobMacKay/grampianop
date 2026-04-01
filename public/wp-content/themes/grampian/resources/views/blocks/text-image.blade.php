@php
  $bg_map = [
    'primary' => 'bg-og-green-200',
    'light'   => 'bg-gray-50',
    'default' => 'bg-white',
  ];
  $bg_class      = $bg_map[$block['background_color'] ?? 'default'] ?? 'bg-white';
  $image         = $block['image'] ?? null;
  $image_right   = ($block['image_position'] ?? 'right') === 'right';
  $call_to_actions = $block['call_to_actions'] ?? [];

  $btn_classes = [
    'primary'   => 'btn btn--primary',
    'secondary' => 'btn btn--secondary',
    'ghost'     => 'inline-flex items-center px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors',
    'link'      => 'inline-flex items-center text-sm font-medium text-blue-700 hover:underline',
  ];
@endphp

<section class="py-10 {{ $bg_class }}">
  <div class="mx-auto max-w-screen-xl px-4">
    <div @class(['flex flex-wrap -mx-4', 'md:flex-row-reverse' => $image && $image_right])>

      @if($image)
        <div class="w-full px-4 mb-6 md:w-1/2 md:mb-0">
          <img
            src="{{ $image['url'] }}"
            alt="{{ $image['alt'] }}"
            width="{{ $image['width'] }}"
            height="{{ $image['height'] }}"
            loading="lazy"
            class="w-full rounded-lg object-cover"
          />
          @if(!empty($block['image_copyright']))
            <small class="text-gray-500">&copy; {{ $block['image_copyright'] }}</small>
          @endif
        </div>
      @endif

      <div @class(['w-full px-4 prose max-w-none', 'md:w-1/2' => $image, 'md:w-3/4' => !$image])>
        @if(!empty($block['title']))
          <h2>{{ $block['title'] }}</h2>
        @endif
        @if(!empty($block['text']))
          {!! $block['text'] !!}
        @endif
        @if($call_to_actions)
          <div class="not-prose flex flex-wrap items-center gap-4 mt-6">
            @foreach($call_to_actions as $cta)
              @php $cls = $btn_classes[$cta['style'] ?? 'primary'] ?? $btn_classes['primary']; @endphp
              <a
                href="{{ $cta['url'] }}"
                class="{{ $cls }}"
                @if(!empty($cta['new_tab'])) target="_blank" rel="noopener" @endif
              >{{ $cta['label'] }}</a>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
