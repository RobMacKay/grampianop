@php
  $item    = $block['booking_item'] ?? null;
  $item_id = $item instanceof \WP_Post ? $item->ID : (int) $item;

  $bookable  = \App\Forms\BookingForm::isBookable($item_id);
  $submitted = \App\Forms\BookingForm::submitted();
@endphp

@if($bookable || ($submitted && $item_id))
  @include('partials.booking-form', [
    'item_id'    => $item_id,
    'form_id'    => 'booking-form-' . substr(md5($block_id ?? uniqid()), 0, 8),
    'is_preview' => $is_preview ?? false,
  ])
@elseif($is_preview ?? false)
  <p class="font-body text-[18px] text-go-ink-soft p-6 border-2 border-dashed border-go-line rounded-[14px]">
    @if($item_id)
      Bookings are not switched on for <strong>{{ get_the_title($item_id) }}</strong> (or it is unpublished or has passed).
      Turn on <em>Take bookings on the website</em> on that item to show the form here.
    @else
      Choose an event or activity for this booking form.
    @endif
  </p>
@endif
