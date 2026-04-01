import Alpine from 'alpinejs';
import 'htmx.org';
import { initFlowbite } from 'flowbite';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => initFlowbite());

import.meta.glob([
  '../images/**',
  '../fonts/**',
]);
