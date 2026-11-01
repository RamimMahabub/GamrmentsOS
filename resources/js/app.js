import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { format, fromKg } from './math';
import { registerCutting } from './cutting';
import { registerForms } from './forms';
import { registerSewing } from './sewing';

window.Alpine = Alpine;
window.GarmentMath = { format, fromKg };
registerForms(Alpine);
registerCutting(Alpine);
registerSewing(Alpine);
Livewire.start();
