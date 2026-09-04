/**
 * De-duplicate input ids across multiple acf_form() instances on one page.
 *
 * ACF derives each input's id from the field key alone (`acf-field_abc123`), so
 * two contact form blocks on the same page emit the same ids twice. Duplicate
 * ids break the label association: clicking the second form's "Name" label
 * focuses the first form's input. ACF has no server-side hook for this, so the
 * ids are rewritten here, on the second and subsequent forms only — leaving the
 * first form untouched keeps ACF's own selectors working.
 */
export default function dedupeFormIds() {
  const forms = Array.from(document.querySelectorAll('form.acf-form'));

  if (forms.length < 2) {
    return;
  }

  forms.slice(1).forEach((form, index) => {
    const suffix = `-${index + 2}`;

    // Rewrite label targets first, while the old ids are still in place.
    form.querySelectorAll('label[for]').forEach((label) => {
      const target = label.getAttribute('for');

      if (form.querySelector(`#${CSS.escape(target)}`)) {
        label.setAttribute('for', target + suffix);
      }
    });

    form.querySelectorAll('[id]').forEach((el) => {
      el.id += suffix;
    });
  });
}
