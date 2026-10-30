<script setup lang="ts">
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { CarFront, SlidersHorizontal } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import { vehicleCollectionUrl } from '@/lib/vehicleCollections';
import type { VehiclePagination } from '@/types/vehicles';

const props = defineProps<{
    vehicles: VehiclePagination; filters: Record<string, string | number | null>;
    makes: string[]; models: string[]; states: string[];
    conditions: Record<string, string>; bodyTypes: Record<string, string>;
    transmissions: Record<string, string>; fuels: Record<string, string>;
}>();
const keys = ['q','make','model','condition','body_type','transmission','fuel_type','state','status','year_min','year_max','price_min','price_max','mileage_max','sort'] as const;
const form = reactive<Record<string, string>>(Object.fromEntries(keys.map(k => [k, String(props.filters[k] ?? '')])));
const showFilters = ref(false);
function search(): void {
    const params: Record<string, string> = {};
    for (const key of keys) if (form[key] && !(key === 'status' && form[key] === 'available')) params[key] = form[key];
    router.get('/automobiles', params, { preserveScroll: true, replace: true });
    showFilters.value = false;
}
function reset(): void { keys.forEach(k => form[k] = ''); search(); }
function openCollection(type: 'compare' | 'saved'): void { router.visit(vehicleCollectionUrl(type)); }
</script>

<template>
    <SeoMeta title="BLOMWHEELS Automobile — Find Your Next Drive" description="Browse real brand-new, foreign-used and Nigerian-used vehicles at BLOMWHEELS. Compare cars and request inspections." />
    <AppLayout>
        <div class="bg-brand-dark px-4 py-16 text-white sm:py-24">
            <div class="mx-auto max-w-7xl">
                <span class="font-bold uppercase tracking-widest text-brand-orange">BLOMWHEELS Automobile</span>
                <h1 class="mt-4 max-w-3xl text-4xl font-extrabold sm:text-6xl">Find Your Next <span class="text-brand-orange">Drive.</span></h1>
                <p class="mt-5 max-w-2xl text-lg text-white/70">Explore brand-new, foreign-used and Nigerian-used vehicles. Compare your options and arrange a viewing with our team.</p>
                <form class="mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row" @submit.prevent="search">
                    <label class="sr-only" for="vehicle-query">Search vehicles</label>
                    <input id="vehicle-query" v-model="form.q" type="search" placeholder="Search by make, model or keyword" class="min-w-0 flex-1 rounded-xl border-0 px-5 py-3 text-brand-dark" />
                    <button type="submit" class="rounded-xl bg-brand-orange px-8 py-3 font-semibold text-white">Search vehicles</button>
                </form>
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-wrap items-center gap-3">
                <button v-for="(label, value) in conditions" :key="value" type="button" class="rounded-full border px-4 py-2 text-sm font-semibold" :class="form.condition === value ? 'border-brand-orange bg-brand-orange text-white' : 'border-gray-200 bg-white'" @click="form.condition = form.condition === value ? '' : String(value); search()">{{ label }}</button>
                <button class="ml-auto rounded-full border border-gray-200 bg-white px-4 py-2 text-sm" @click="openCollection('saved')">Saved vehicles</button>
                <button class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm" @click="openCollection('compare')">Compare vehicles</button>
            </div>
            <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
                <aside>
                    <button type="button" class="mb-3 flex w-full items-center justify-center gap-2 rounded-xl bg-brand-dark px-4 py-3 font-semibold text-white lg:hidden" @click="showFilters = !showFilters"><SlidersHorizontal class="h-5 w-5" /> {{ showFilters ? 'Hide filters' : 'Show filters' }}</button>
                    <form class="space-y-4 rounded-2xl bg-white p-5 ring-1 ring-black/5" :class="showFilters ? 'block' : 'hidden lg:block'" @submit.prevent="search">
                        <h2 class="font-bold">Filter vehicles</h2>
                        <div v-for="filter in [
                            { key: 'make', label: 'Make', options: makes },
                            { key: 'model', label: 'Model', options: models },
                            { key: 'state', label: 'State', options: states }
                        ]" :key="filter.key">
                            <label :for="filter.key" class="mb-1 block text-sm font-medium">{{ filter.label }}</label>
                            <select :id="filter.key" v-model="form[filter.key]" class="w-full rounded-xl border-gray-300 text-sm"><option value="">All</option><option v-for="option in filter.options" :key="option" :value="option">{{ option }}</option></select>
                        </div>
                        <div v-for="filter in [
                            { key: 'body_type', label: 'Body type', options: bodyTypes },
                            { key: 'transmission', label: 'Transmission', options: transmissions },
                            { key: 'fuel_type', label: 'Fuel type', options: fuels }
                        ]" :key="filter.key">
                            <label :for="filter.key" class="mb-1 block text-sm font-medium">{{ filter.label }}</label>
                            <select :id="filter.key" v-model="form[filter.key]" class="w-full rounded-xl border-gray-300 text-sm"><option value="">All</option><option v-for="(option, value) in filter.options" :key="value" :value="value">{{ option }}</option></select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="field in [
                                { key: 'year_min', label: 'Year from' }, { key: 'year_max', label: 'Year to' },
                                { key: 'price_min', label: 'Min price (₦)' }, { key: 'price_max', label: 'Max price (₦)' },
                                { key: 'mileage_max', label: 'Max km' }
                            ]" :key="field.key">
                                <label :for="field.key" class="mb-1 block text-xs font-semibold">{{ field.label }}</label>
                                <input :id="field.key" v-model="form[field.key]" type="number" min="0" class="w-full rounded-xl border-gray-300 text-sm" />
                            </div>
                        </div>
                        <label class="block text-sm font-medium" for="vehicle-status">Availability</label>
                        <select id="vehicle-status" v-model="form.status" class="w-full rounded-xl border-gray-300 text-sm">
                            <option value="">Available</option><option value="reserved">Reserved</option><option value="sold">Sold</option><option value="on_order">On order</option>
                        </select>
                        <button type="submit" class="w-full rounded-xl bg-brand-orange py-3 font-semibold text-white">Apply filters</button>
                        <button type="button" class="w-full py-2 text-sm font-semibold text-brand-dark/70" @click="reset">Clear all filters</button>
                    </form>
                </aside>
                <section>
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-xl font-bold">{{ vehicles.total }} vehicle{{ vehicles.total === 1 ? '' : 's' }} found</h2>
                        <select v-model="form.sort" aria-label="Sort vehicles" class="rounded-xl border-gray-300 text-sm" @change="search">
                            <option value="">Newest first</option><option value="price_asc">Price: low to high</option><option value="price_desc">Price: high to low</option><option value="year_desc">Year: newest first</option><option value="year_asc">Year: oldest first</option><option value="mileage_asc">Mileage: lowest first</option>
                        </select>
                    </div>
                    <div v-if="vehicles.data.length" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <VehicleCard v-for="vehicle in vehicles.data" :key="vehicle.id" :vehicle="vehicle" />
                    </div>
                    <div v-else class="rounded-3xl border border-dashed border-gray-300 bg-white px-5 py-20 text-center">
                        <CarFront class="mx-auto h-10 w-10 text-brand-orange" />
                        <h3 class="mt-4 text-xl font-bold">No vehicles match these filters</h3>
                        <p class="mt-2 text-brand-dark/60">Try a different search or contact BLOMWHEELS for availability.</p>
                        <button class="mt-5 font-bold text-brand-orange" @click="reset">Show all available vehicles</button>
                    </div>
                    <nav v-if="vehicles.last_page > 1" aria-label="Vehicle pagination" class="mt-8 flex flex-wrap gap-2">
                        <Link v-for="(link, index) in vehicles.links" :key="index" :href="link.url || '#'" class="rounded-lg border px-3 py-2 text-sm" :class="link.active ? 'border-brand-orange bg-brand-orange text-white' : 'bg-white'" :aria-disabled="!link.url" :preserve-scroll="true">{{ link.label.replace('&laquo;', '«').replace('&raquo;', '»') }}</Link>
                    </nav>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
