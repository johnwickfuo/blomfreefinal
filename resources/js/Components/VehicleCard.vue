<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Heart, GitCompareArrows, MapPin } from 'lucide-vue-next';
import type { VehicleCardData } from '@/types/vehicles';
import { conditionLabel, formatVehiclePrice } from '@/types/vehicles';
import { readVehicleIds, toggleVehicleId } from '@/lib/vehicleCollections';

const props = defineProps<{ vehicle: VehicleCardData }>();
const saved = ref(false);
const compared = ref(false);
function sync(): void {
    saved.value = readVehicleIds('blomwheels-saved').includes(props.vehicle.id);
    compared.value = readVehicleIds('blomwheels-compare').includes(props.vehicle.id);
}
function save(): void { toggleVehicleId('blomwheels-saved', props.vehicle.id); sync(); }
function compare(): void {
    const current = readVehicleIds('blomwheels-compare');
    if (!current.includes(props.vehicle.id) && current.length >= 3) {
        window.alert('You can compare up to three vehicles.'); return;
    }
    toggleVehicleId('blomwheels-compare', props.vehicle.id, 3); sync();
}
onMounted(() => { sync(); window.addEventListener('blomwheels-list-changed', sync); });
</script>

<template>
    <article class="flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-black/5 transition-shadow hover:shadow-lg">
        <Link :href="vehicle.href" class="block aspect-[16/10] overflow-hidden bg-brand-dark/10">
            <img v-if="vehicle.image" :src="vehicle.image" :alt="vehicle.title" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 hover:scale-105" />
            <div v-else class="flex h-full items-center justify-center text-sm text-brand-dark/50">Vehicle photos coming soon</div>
        </Link>
        <div class="flex flex-1 flex-col gap-3 p-5">
            <div class="flex flex-wrap gap-2 text-xs font-semibold">
                <span class="rounded-full bg-orange-50 px-3 py-1 text-brand-orange">{{ conditionLabel(vehicle.condition) }}</span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-brand-dark/70">{{ vehicle.status }}</span>
            </div>
            <Link :href="vehicle.href" class="text-lg font-bold hover:text-brand-orange">{{ vehicle.year }} {{ vehicle.title }}</Link>
            <p class="text-sm text-brand-dark/65">{{ vehicle.mileage_km === null ? 'Mileage not listed' : vehicle.mileage_km.toLocaleString() + ' km' }} · {{ vehicle.transmission ?? 'Transmission not listed' }}</p>
            <p class="flex items-center gap-1 text-sm text-brand-dark/60"><MapPin class="h-4 w-4" /> {{ [vehicle.city, vehicle.state].filter(Boolean).join(', ') || 'Location on request' }}</p>
            <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4">
                <strong class="text-xl text-brand-orange">{{ formatVehiclePrice(vehicle.price) }}</strong>
                <div class="flex gap-1">
                    <button type="button" :aria-label="saved ? 'Remove saved vehicle' : 'Save vehicle'" :aria-pressed="saved" class="rounded-full p-2 hover:bg-orange-50" @click="save"><Heart class="h-5 w-5" :class="saved ? 'fill-brand-orange text-brand-orange' : 'text-gray-500'" /></button>
                    <button type="button" :aria-label="compared ? 'Remove from comparison' : 'Compare vehicle'" :aria-pressed="compared" class="rounded-full p-2 hover:bg-orange-50" @click="compare"><GitCompareArrows class="h-5 w-5" :class="compared ? 'text-brand-orange' : 'text-gray-500'" /></button>
                </div>
            </div>
            <Link :href="vehicle.href" class="rounded-xl bg-brand-dark px-4 py-3 text-center text-sm font-semibold text-white hover:bg-brand-orange">View vehicle details</Link>
        </div>
    </article>
</template>
