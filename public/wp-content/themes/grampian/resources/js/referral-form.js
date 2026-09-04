/**
 * Multi-step layer over an acf_form().
 *
 * ACF already groups fields by Tab field on the front end — it marks the fields
 * of every non-active tab with `.acf-hidden` — so the stepping itself is ACF's
 * job and this file just drives it. Each tab field exposes `tab.open()`, which
 * shows its group and closes the others.
 *
 * One trap worth knowing: `open()` only closes sibling tabs it considers
 * visible, so hiding ACF's tab nav with `display: none` breaks the grouping and
 * dumps every step on screen at once. go-form.css clips the nav instead.
 *
 * Conditional logic also stays with ACF. The referrer tab is configured to show
 * only for third-party referrals, so ACF marks that tab element itself
 * `.acf-hidden` for a self-referral — which is what drops the step out of the
 * sequence below. Nothing here knows what a referral is.
 */
export default function referralForm(labelOverrides = []) {
  return {
    /** Index into the *visible* step list, not into all tabs. */
    stepIndex: 0,

    /** ACF tab field objects, in document order. */
    tabs: [],

    /** Indexes into `tabs` whose tab is not hidden by conditional logic. */
    steps: [],

    labelOverrides,

    init() {
      // ACF boots asynchronously; wait for it before reaching for its API.
      const start = () => {
        if (!window.acf) {
          window.setTimeout(start, 50);

          return;
        }

        this.tabs = window.acf.getFields({ type: 'tab' });
        this.relocateSubmit();
        this.recalculate();

        // Conditional logic can add or remove a whole step, so re-evaluate on
        // any change rather than watching one specific field.
        this.$el.addEventListener('change', () => this.recalculate());
      };

      start();
    },

    /**
     * ACF renders its submit button at the end of the form. Move it into the
     * step controls so Back / Next / Submit share a row. It stays inside the
     * <form>, which is why the slot is injected via html_after_fields.
     */
    relocateSubmit() {
      const submit = this.$el.querySelector('.acf-form-submit');
      const slot = this.$el.querySelector('[data-go-submit-slot]');

      if (submit && slot) {
        slot.appendChild(submit);
      }
    },

    /**
     * A tab is part of the sequence unless conditional logic has hidden the tab
     * itself. Tab elements are never hidden by tab-switching, only by
     * conditional logic, so this reads cleanly.
     */
    isApplicable(tab) {
      return !tab.$el.hasClass('acf-hidden');
    },

    /** Rebuild the step list, keeping the user on the same tab where possible. */
    recalculate() {
      const current = this.steps[this.stepIndex];

      this.steps = this.tabs
        .map((_, index) => index)
        .filter((index) => this.isApplicable(this.tabs[index]));

      const next = this.steps.indexOf(current);

      this.stepIndex = next === -1
        ? Math.max(0, Math.min(this.stepIndex, this.steps.length - 1))
        : next;

      this.show();
    },

    /**
     * Switch to the active step's tab.
     *
     * Note `tab.open()` is NOT the entry point — it only adds `.active` and
     * shows its own fields, leaving the previous tab open too, which stacks
     * every step on screen. Closing the outgoing tab is the group's job, and
     * `toggle()` is what routes through it (group.openTab → closeActive → open).
     */
    show() {
      const tab = this.tabs[this.steps[this.stepIndex]]?.tab;

      if (tab && !tab.isActive()) {
        tab.toggle();
      }
    },

    get labels() {
      return this.steps.map(
        (index) => this.labelOverrides[index] || this.tabs[index]?.get('label') || ''
      );
    },

    /** Counts the current step as underway, so step 1 of 4 reads 25%, not 0%. */
    get progress() {
      if (this.steps.length === 0) {
        return 0;
      }

      return ((this.stepIndex + 1) / this.steps.length) * 100;
    },

    get isLastStep() {
      return this.stepIndex >= this.steps.length - 1;
    },

    next() {
      if (!this.validateStep()) {
        return;
      }

      if (this.stepIndex < this.steps.length - 1) {
        this.stepIndex += 1;
        this.show();
        this.focusStep();
      }
    },

    back() {
      if (this.stepIndex > 0) {
        this.stepIndex -= 1;
        this.show();
        this.focusStep();
      }
    },

    /**
     * Every field actually on screen — the active step's fields, minus anything
     * conditional logic has hidden. The offsetParent check also drops ACF's
     * honeypot, which is hidden with an inline style rather than a class.
     */
    visibleFields() {
      return Array.from(
        this.$el.querySelectorAll('.acf-field:not(.acf-hidden):not(.acf-field-tab)')
      ).filter((field) => field.offsetParent !== null);
    },

    /**
     * Validate only what is on screen before advancing. ACF's own server-side
     * validation still runs on final submit, so this is a courtesy, not the
     * source of truth.
     */
    validateStep() {
      for (const field of this.visibleFields()) {
        for (const input of field.querySelectorAll('input, select, textarea')) {
          if (input.type === 'hidden' || input.disabled) {
            continue;
          }

          if (!input.checkValidity()) {
            input.reportValidity();

            return false;
          }
        }
      }

      return true;
    },

    /** Move focus to the step heading so screen readers follow the change. */
    focusStep() {
      this.$nextTick(() => {
        this.$refs.stepHeading?.focus();

        const top = this.$el.getBoundingClientRect().top + window.scrollY - 24;
        window.scrollTo({ top, behavior: 'smooth' });
      });
    },
  };
}
