{{--
  Template Name: Referral Form
--}}

@extends('layouts.app')

@php
  $submitted        = isset($_GET['submitted']) && $_GET['submitted'] === '1';
  $intro            = get_field('referral_intro');
  $success_heading  = get_field('referral_success_heading') ?: 'Referral received';
  $success_body     = get_field('referral_success_body')
      ?: 'Thank you. Someone from the team will review this referral and be in touch.';
  $privacy_page     = get_field('referral_privacy_page');
  $phone            = get_field('phone', 'option') ?: '01467 629675';

  // Step labels drive the progress bar. The keys match the ACF tab order, so
  // renaming a tab in ACF does not silently desync the labels shown here.
  $step_labels = collect(get_field('referral_step_labels') ?: [])
      ->pluck('label')
      ->filter()
      ->values()
      ->all();

  if (empty($step_labels)) {
      $step_labels = ['Referral type', 'Your details', 'About the person', 'Referral details'];
  }
@endphp

@section('content')
  @while(have_posts())
    @php the_post() @endphp

    <section class="go-block not-prose bg-go-cream px-6 py-[72px]">
      <div class="max-w-[820px] mx-auto">

        <h1 class="font-heading font-bold text-go-ink tracking-[-1.2px] mb-4"
            style="font-size: clamp(34px, 4vw, 52px)">
          {{ get_the_title() }}
        </h1>

        @if($submitted)

          {{-- Success --}}
          <div class="rounded-[22px] bg-white border border-go-line border-l-[8px] border-l-go-green p-[36px_32px]">
            <h2 class="font-heading font-bold text-[26px] text-go-ink mb-3">{{ $success_heading }}</h2>
            <div class="font-body text-[18px] leading-[1.6] text-go-ink-soft">
              {!! wpautop($success_body) !!}
            </div>
            <p class="font-body text-[18px] leading-[1.6] text-go-ink-soft mt-4">
              If you need to speak to us sooner, call
              <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
                 class="font-bold text-go-green-deep underline decoration-2 underline-offset-[3px] hover:text-go-green-deepest">{{ $phone }}</a>.
            </p>
            <a href="{{ home_url('/') }}"
               class="mt-7 inline-flex items-center px-[26px] py-4 rounded-full bg-go-green-deep text-white font-heading font-bold text-[17px] leading-none hover:bg-go-green-deepest transition-colors">
              Back to the homepage
            </a>
          </div>

        @else

          @if($intro)
            <div class="font-body text-[19px] leading-[1.6] text-go-ink-soft mb-9 [&_a]:font-bold [&_a]:text-go-green-deep [&_a]:underline [&_a]:decoration-2 [&_a]:underline-offset-[3px]">
              {!! $intro !!}
            </div>
          @endif

          <div
            class="go-referral rounded-[22px] bg-white border border-go-line overflow-hidden"
            x-data="referralForm({{ Illuminate\Support\Js::from($step_labels) }})"
            x-cloak
          >

            {{-- Progress --}}
            <div class="px-6 sm:px-8 md:px-10 pt-8 pb-6 border-b border-go-line">
              <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
                <p class="font-heading font-bold text-[18px] text-go-ink focus:outline-none"
                   x-ref="stepHeading" tabindex="-1" aria-live="polite"
                   x-text="labels[stepIndex]"></p>
                <p class="font-body text-[17px] text-go-ink-soft"
                   x-text="`Step ${stepIndex + 1} of ${steps.length}`"></p>
              </div>
              <div
                class="h-2.5 w-full rounded-full bg-go-mint"
                role="progressbar"
                aria-label="Referral progress"
                :aria-valuenow="stepIndex + 1"
                aria-valuemin="1"
                :aria-valuemax="steps.length"
              >
                <div class="h-2.5 rounded-full bg-go-green transition-all duration-300"
                     :style="`width: ${progress}%`"></div>
              </div>
            </div>

            {{-- ACF renders every field; the stepper shows one tab group at a time.
                 The step controls go in via html_after_fields so they sit inside
                 the <form> — the submit button is relocated into the slot below
                 and must stay within the form to submit it. --}}
            <div class="px-6 sm:px-8 md:px-10 py-8">
              @php
                $controls = <<<'HTML'
                <div class="go-form__controls">
                  <button type="button" x-show="stepIndex > 0" @click="back()"
                    class="inline-flex items-center px-[26px] py-4 rounded-full border-2 border-go-green-deep text-go-green-deep font-heading font-bold text-[17px] leading-none hover:bg-go-green-deep hover:text-white transition-colors">
                    Back
                  </button>
                  <button type="button" x-show="!isLastStep" @click="next()"
                    class="ml-auto inline-flex items-center px-[26px] py-4 rounded-full bg-go-green-deep text-white font-heading font-bold text-[17px] leading-none hover:bg-go-green-deepest transition-colors">
                    Next
                  </button>
                  <div class="ml-auto" x-show="isLastStep" x-cloak data-go-submit-slot></div>
                </div>
                HTML;

                acf_form([
                    'id'                => 'referral-form',
                    'post_id'           => 'new_post',
                    'new_post'          => [
                        'post_type'   => 'referral',
                        'post_status' => 'pending',
                    ],
                    'field_groups'      => ['group_grampian_referral_fields'],
                    'form_attributes'   => ['class' => 'acf-form go-form'],
                    'submit_value'      => 'Submit referral',
                    'honeypot'          => true,
                    'updated_message'   => false,
                    'html_after_fields' => $controls,
                    'return'            => add_query_arg('submitted', '1', get_permalink()),
                ]);
              @endphp

              @if($privacy_page)
                <p class="font-body text-[17px] leading-[1.6] text-go-ink-soft mt-6">
                  Read our
                  <a href="{{ $privacy_page }}"
                     class="font-bold text-go-green-deep underline decoration-2 underline-offset-[3px] hover:text-go-green-deepest">privacy policy</a>
                  to see how we look after your information.
                </p>
              @endif
            </div>

          </div>

        @endif

      </div>
    </section>

  @endwhile
@endsection
