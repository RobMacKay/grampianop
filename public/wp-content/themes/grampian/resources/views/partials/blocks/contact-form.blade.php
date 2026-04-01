{{--
  Contact form block.
  Install Contact Form 7 and replace the shortcode ID below,
  or swap for WPForms / Gravity Forms as preferred.

  To find your CF7 form ID: Forms → Contact Forms → hover the title → note the ID in the URL.
--}}
<section class="py-10" id="contact-form">
  <div class="mx-auto max-w-screen-xl px-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div>
        {!! do_shortcode('[contact-form-7 id="REPLACE_WITH_CF7_ID" title="Contact Form"]') !!}
      </div>
      @include('partials.blocks.google-map')
    </div>
  </div>
</section>
