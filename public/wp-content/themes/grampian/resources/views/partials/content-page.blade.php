<article @php(post_class('mx-auto max-w-screen-xl px-4 py-8'))>
  <div class="prose max-w-none">
    @php(the_content())
  </div>

  @if ($pagination)
    <nav class="mt-8 flex justify-center gap-2" aria-label="{{ __('Page', 'sage') }}">
      {!! $pagination !!}
    </nav>
  @endif
</article>
