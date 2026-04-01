<section class="py-10">
  <div class="mx-auto max-w-screen-xl px-4">
    @if(!empty($block['title']))
      <h2 class="text-2xl font-semibold mb-4">{{ $block['title'] }}</h2>
    @endif
    @if(!empty($block['url']))
      <div class="aspect-video w-full max-w-3xl mx-auto">
        {!! wp_oembed_get($block['url'], ['width' => 900]) !!}
      </div>
    @endif
  </div>
</section>
