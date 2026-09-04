import Alpine from 'alpinejs';
import 'htmx.org';
import { initFlowbite } from 'flowbite';
import referralForm from './referral-form';
import dedupeFormIds from './form-ids';

window.Alpine = Alpine;

Alpine.data('referralForm', referralForm);

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
  initFlowbite();
  dedupeFormIds();
});

import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
