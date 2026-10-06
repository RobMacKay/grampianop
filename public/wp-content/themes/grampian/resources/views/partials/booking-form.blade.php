{{--
  Booking form for an event or activity.

  Expects: $item_id (int), $form_id (string), $is_preview (bool).
  Callers only include this when the item is bookable, or when the visitor has
  just booked (so they still see the confirmation).
--}}
@php
  $is_preview = $is_preview ?? false;
  $submitted  = \App\Forms\BookingForm::submitted();
  $phone      = get_field('phone', 'option') ?: '01467 629675';
  $intro      = (string) get_field('booking_intro', $item_id);
  $item_title = get_the_title($item_id);

  // Places: a one-off event has a single count; anything else is counted per date,
  // which booking-spaces.js looks up once the visitor picks one.
  $limit     = \App\Forms\BookingSpaces::limit($item_id);
  $one_off   = \App\Forms\BookingSpaces::isOneOff($item_id);
  $left_now  = ($limit !== null && $one_off)
      ? \App\Forms\BookingSpaces::left($item_id, \App\Forms\BookingSpaces::sessionDate($item_id))
      : null;
  $is_full   = $left_now === 0;

  // For an event, say when it is. One-off events need no date field — this is the date.
  $when = '';
  if (get_post_type($item_id) === 'event' && ($next = \App\EventRecurrence::nextCard($item_id))) {
      $when = trim(ucfirst(strtolower($next['weekday'])) . ' ' . $next['day'] . ($next['detail'] ? ' · ' . $next['detail'] : ''));
  }
@endphp

<section id="book" class="go-block not-prose bg-go-cream px-6 py-[72px]">
  <div class="max-w-[820px] mx-auto">

    @if($submitted)

      <div class="rounded-[22px] bg-white border border-go-line border-l-[8px] border-l-go-green p-6 sm:p-8">
        <h2 class="font-heading font-bold text-[26px] text-go-ink mb-3">Booking request sent</h2>
        <p class="font-body text-[18px] leading-[1.6] text-go-ink-soft">
          Thank you. We will be in touch to confirm your place.
        </p>
        <p class="font-body text-[18px] leading-[1.6] text-go-ink-soft mt-4">
          Need to change something? Call us on
          <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
             class="font-bold text-go-green-deep underline decoration-2 underline-offset-[3px] hover:text-go-green-deepest">{{ $phone }}</a>.
        </p>
      </div>

    @else

      <h2 class="font-heading font-bold text-go-ink tracking-[-0.6px] mb-3"
          style="font-size: clamp(30px, 3vw, 40px)">
        Book a place
      </h2>

      <p class="font-heading font-bold text-[20px] text-go-green-deep mb-2">{{ $item_title }}</p>

      @if($when)
        <p class="font-body text-[18px] leading-[1.6] text-go-ink-soft mb-2">{{ $when }}</p>
      @endif

      @if($limit !== null)
        <p class="font-heading font-bold text-[18px] mb-2 {{ $is_full ? 'text-go-red' : 'text-go-green-deep' }}" role="status">
          @if($left_now !== null)
            {{ $is_full ? 'Fully booked' : $left_now . ($left_now === 1 ? ' place left' : ' places left') }}
          @else
            Places are limited — we will show how many are left once you choose a date.
          @endif
        </p>
      @endif

      @if($intro)
        <p class="font-body text-[19px] leading-[1.6] text-go-ink-soft mb-2">{{ $intro }}</p>
      @endif

      <div class="rounded-[22px] bg-white border border-go-line p-6 sm:p-8 md:p-10 mt-8">
        @if($is_full)
          <p class="font-body text-[18px] leading-[1.6] text-go-ink-soft">
            Sorry, this is fully booked. Call us on
            <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
               class="font-bold text-go-green-deep underline decoration-2 underline-offset-[3px] hover:text-go-green-deepest">{{ $phone }}</a>
            to ask about the waiting list.
          </p>
        @elseif($is_preview)
          {{-- acf_form() cannot render inside the editor preview iframe. --}}
          <p class="font-body text-[18px] text-go-ink-soft">
            The booking form for <strong>{{ $item_title }}</strong> renders here on the published page.
            Fields are managed on the <strong>Booking Details</strong> field group in ACF.
          </p>
        @else
          @php
            \App\Forms\BookingForm::$currentItem = $item_id;

            acf_form([
                'id'                 => $form_id,
                'post_id'            => 'new_post',
                'new_post'           => [
                    'post_type'   => 'booking',
                    'post_status' => 'pending',
                ],
                'field_groups'       => ['group_grampian_booking_fields'],
                'form_attributes'    => ['class' => 'acf-form go-form'],
                'html_before_fields' => sprintf(
                    '<input type="hidden" name="go_booking_item" value="%d"%s>',
                    $item_id,
                    ($limit !== null && ! $one_off)
                        ? ' data-spaces-url="' . esc_url(admin_url('admin-ajax.php')) . '"'
                        : ''
                ),
                'submit_value'       => 'Send booking request',
                'honeypot'           => true,
                'updated_message'    => false,
                'return'             => add_query_arg('booked', '1', get_permalink()) . '#book',
            ]);

            \App\Forms\BookingForm::$currentItem = null;
          @endphp
        @endif
      </div>

    @endif

  </div>
</section>
