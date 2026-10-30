<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import type { VehicleCardData } from '@/types/vehicles';
import { readVehicleIds, vehicleCollectionUrl } from '@/lib/vehicleCollections';
defineProps<{ vehicles: VehicleCardData[] }>();
function refresh(): void { router.visit(vehicleCollectionUrl('saved'), { preserveScroll: true }); }
onMounted(() => {
    if (!window.location.search && readVehicleIds('blomwheels-saved').length) refresh();
    window.addEventListener('blomwheels-list-changed', refresh);
});
onBeforeUnmount(() => window.removeEventListener('blomwheels-list-changed', refresh));
</script>
<template>
    <SeoMeta title="Saved Vehicles — BLOMWHEELS" description="Your saved vehicle listings." />
    <AppLayout><div class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <Link href="/automobiles" class="font-semibold text-brand-orange">← Browse vehicles</Link>
        <h1 class="mt-5 text-3xl font-extrabold">Saved vehicles</h1>
        <p class="mt-2 text-brand-dark/60">Saved on this device. No account required.</p>
        <div v-if="vehicles.length" class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"><VehicleCard v-for="vehicle in vehicles" :key="vehicle.id" :vehicle="vehicle" /></div>
        <p v-else class="mt-8 rounded-2xl bg-white p-10 text-center">You have not saved any vehicles yet.</p>
    </div></AppLayout>
</template>
