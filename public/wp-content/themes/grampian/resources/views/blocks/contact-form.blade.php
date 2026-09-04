@php
  $heading         = $block['heading']         ?? 'Send us a message';
  $heading_level   = $block['heading_level']   ?? 'h2';
  $intro           = $block['intro']           ?? '';
  $success_heading = $block['success_heading'] ?? 'Message sent';
  $success_body    = $block['success_body']
      ?? 'Thanks for getting in touch — we will get back to you as soon as we can.';
  $phone           = get_field('phone', 'option') ?: '01467 629675';

  $submitted = isset($_GET['contact_sent']) && $_GET['contact_sent'] === '1';

  // Each instance needs its own form id — two contact blocks can sit on one page.
  $form_id = 'contact-form-' . substr(md5($block_id ?? uniqid()), 0, 8);
@endphp

<section class="go-block not-prose bg-white px-6 py-[72px]">
  <div class="max-w-[820px] mx-auto">

    @if($submitted)

      <div class="rounded-[22px] bg-go-mint border border-go-line border-l-[8px] border-l-go-green p-[36px_32px]">
        <h2 class="font-heading font-bold text-[26px] text-go-ink mb-3">{{ $success_heading }}</h2>
        <p class="font-body text-[18px] leading-[1.6] text-go-ink-soft">{{ $success_body }}</p>
        <p class="font-body text-[18px] leading-[1.6] text-go-ink-soft mt-4">
          Prefer to talk? Call us on
          <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
             class="font-bold text-go-green-deep underline decoration-2 underline-offset-[3px] hover:text-go-green-deepest">{{ $phone }}</a>.
        </p>
      </div>

    @else

      <{{ $heading_level }} class="font-heading font-bold text-go-ink tracking-[-0.6px] mb-4"
        style="font-size: clamp(30px, 3vw, 40px)">
        {!! $heading !!}
      </{{ $heading_level }}>

      @if($intro)
        <div class="font-body text-[19px] leading-[1.6] text-go-ink-soft mb-8">{!! $intro !!}</div>
      @endif

      <div class="rounded-[22px] bg-white border border-go-line p-[32px]">
        @if($is_preview)
          {{-- acf_form() cannot render inside the editor preview iframe (it needs
               acf_form_head(), which only runs on the front end), so show the
               shape of the block instead of a broken form. --}}
          <p class="font-body text-[18px] text-go-ink-soft">
            The contact form renders here on the published page. Fields are managed on the
            <strong>Contact Submission</strong> field group in ACF.
          </p>
        @else
          @php
            acf_form([
                'id'              => $form_id,
                'post_id'         => 'new_post',
                'new_post'        => [
                    'post_type'   => 'contact_submission',
                    'post_status' => 'pending',
                ],
                'field_groups'    => ['group_grampian_contact_fields'],
                'form_attributes' => ['class' => 'acf-form go-form'],
                'submit_value'    => 'Send message',
                'honeypot'        => true,
                'updated_message' => false,
                'return'          => add_query_arg('contact_sent', '1', get_permalink()),
            ]);
          @endphp
        @endif
      </div>

    @endif

  </div>
</section>
