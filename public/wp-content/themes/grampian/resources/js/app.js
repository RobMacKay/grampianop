import Alpine from 'alpinejs';
import 'htmx.org';
import { initFlowbite } from 'flowbite';
import referralForm from './referral-form';
import dedupeFormIds from './form-ids';
import initTurnstile from './turnstile';
import initBookingSpaces from './booking-spaces';

window.Alpine = Alpine;

Alpine.data('referralForm', referralForm);

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
  initFlowbite();
  dedupeFormIds();
  initTurnstile();
  initBookingSpaces();
});

import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
