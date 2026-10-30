<script setup lang="ts">
import { onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import type { VehicleDetails } from '@/types/vehicles';
import { formatVehiclePrice, conditionLabel } from '@/types/vehicles';
import { readVehicleIds, toggleVehicleId, vehicleCollectionUrl } from '@/lib/vehicleCollections';
defineProps<{ vehicles: VehicleDetails[] }>();
const fields: { key: keyof VehicleDetails; label: string }[] = [
    { key:'price',label:'Price' },{key:'year',label:'Year'},{key:'condition',label:'Condition'},
    {key:'mileage_km',label:'Mileage (km)'},{key:'transmission',label:'Transmission'},
    {key:'fuel_type',label:'Fuel'},{key:'engine',label:'Engine'},{key:'drivetrain',label:'Drivetrain'},
    {key:'body_type',label:'Body type'},{key:'city',label:'City'},{key:'state',label:'State'}, {key:'status',label:'Availability'}
];
function value(vehicle: VehicleDetails, key: keyof VehicleDetails): string {
    const v = vehicle[key];
    if (v == null || v === '') return '—';
    if (key === 'price') return formatVehiclePrice(Number(v));
    if (key === 'condition') return conditionLabel(String(v));
    return String(v);
}
function remove(id: number): void { toggleVehicleId('blomwheels-compare', id, 3); router.visit(vehicleCollectionUrl('compare')); }
onMounted(() => {
    if (!window.location.search && readVehicleIds('blomwheels-compare').length) router.visit(vehicleCollectionUrl('compare'));
});
</script>
<template>
    <SeoMeta title="Compare Vehicles — BLOMWHEELS" description="Compare vehicle prices and specifications side by side." />
    <AppLayout><div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <Link href="/automobiles" class="font-semibold text-brand-orange">← Browse vehicles</Link>
        <h1 class="mt-5 text-3xl font-extrabold sm:text-4xl">Compare vehicles</h1>
        <p class="mt-2 text-brand-dark/65">Compare up to three published vehicle listings.</p>
        <div v-if="!vehicles.length" class="mt-8 rounded-2xl bg-white p-10 text-center">No vehicles selected. Add vehicles from the catalogue.</div>
        <div v-else class="mt-8 overflow-x-auto rounded-2xl bg-white shadow-sm">
            <table class="w-full min-w-[650px] border-collapse text-left text-sm">
                <thead><tr><th class="w-36 border-b p-4">Vehicle</th><th v-for="vehicle in vehicles" :key="vehicle.id" class="border-b p-4 align-top">
                    <img v-if="vehicle.image" :src="vehicle.image" :alt="vehicle.title" class="mb-3 aspect-video w-48 rounded-lg object-cover" />
                    <Link :href="vehicle.href" class="block font-bold text-brand-orange">{{ vehicle.title }}</Link>
                    <button class="mt-2 text-xs underline" @click="remove(vehicle.id)">Remove</button>
                </th></tr></thead>
                <tbody><tr v-for="field in fields" :key="field.key" class="border-b last:border-0"><th class="bg-gray-50 p-4 font-semibold">{{ field.label }}</th><td v-for="vehicle in vehicles" :key="vehicle.id" class="p-4">{{ value(vehicle, field.key) }}</td></tr></tbody>
            </table>
        </div>
    </div></AppLayout>
</template>
