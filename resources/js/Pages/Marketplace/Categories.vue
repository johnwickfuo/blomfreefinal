<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Smartphone, Shirt, CarFront, MapPin, PawPrint, ArrowRight, Search } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { divisionLinks } from '@/types/marketplace';
defineProps<{ productCategories: { gadgets: string[]; collections: string[] }; vehicleMakes: string[]; propertyStates: string[] }>();
const icons = [Smartphone, Shirt, CarFront, MapPin, PawPrint];
const links = [
    { label:'Gadgets & Accessories', key:'gadgets', href:'/gadgets' },
    { label:'Fashion & Collections', key:'collections', href:'/collections' },
    { label:'Automobiles', key:'vehicles', href:'/automobiles' },
    { label:'Real Estate & Land', key:'lands', href:'/lands' },
    { label:'Kennel & Farm', key:'animals', href:'/kennel-farm' },
];
const animals = [{label:'Dogs', category:'dog'},{label:'Cats',category:'cat'},{label:'Rabbits',category:'rabbit'},{label:'Grasscutters',category:'grasscutter'}];
</script>
<template>
    <SeoMeta title="Shop Categories — BLOMFREE Marketplace" description="Explore fashion, gadgets, automobiles, real estate and animals across BLOMFREE." />
    <AppLayout><div class="mx-auto max-w-7xl px-4 py-8 pb-16 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold sm:text-4xl">Shop by category</h1>
        <p class="mt-3 max-w-2xl text-brand-dark/65">Find what you need across BLOMFREE's five divisions.</p>
        <Link href="/search" class="mt-5 flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-sm text-brand-dark/65 ring-1 ring-black/5">
            <Search class="h-5 w-5 text-brand-orange" /> Search all listings <ArrowRight class="ml-auto h-4 w-4" />
        </Link>
        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <section v-for="(division,i) in links" :key="division.key" class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex items-center gap-3"><span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-cream text-brand-orange"><component :is="icons[i]" class="h-6 w-6" /></span>
                    <div><h2 class="text-lg font-extrabold">{{ division.label }}</h2><Link :href="division.href" class="text-sm font-bold text-brand-orange">View all →</Link></div>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    <template v-if="division.key === 'gadgets' || division.key === 'collections'">
                        <Link v-for="category in productCategories[division.key]" :key="category"
                            :href="division.href + '?category=' + encodeURIComponent(category)"
                            class="rounded-full border border-gray-200 px-3 py-2 text-xs font-semibold hover:border-brand-orange hover:text-brand-orange">{{ category }}</Link>
                    </template>
                    <template v-else-if="division.key === 'vehicles'">
                        <Link v-for="make in vehicleMakes.slice(0,24)" :key="make"
                            :href="division.href + '?make=' + encodeURIComponent(make)"
                            class="rounded-full border border-gray-200 px-3 py-2 text-xs font-semibold hover:border-brand-orange">{{ make }}</Link>
                    </template>
                    <template v-else-if="division.key === 'lands'">
                        <Link v-for="state in propertyStates" :key="state" :href="division.href + '?state=' + encodeURIComponent(state)"
                            class="rounded-full border border-gray-200 px-3 py-2 text-xs font-semibold hover:border-brand-orange">{{ state }}</Link>
                    </template>
                    <template v-else>
                        <Link v-for="animal in animals" :key="animal.category"
                            :href="division.href + '?category=' + animal.category"
                            class="rounded-full border border-gray-200 px-3 py-2 text-xs font-semibold hover:border-brand-orange">{{ animal.label }}</Link>
                    </template>
                    <p v-if="(division.key === 'gadgets' || division.key === 'collections') && !productCategories[division.key].length" class="text-sm text-brand-dark/55">Browse all available listings.</p>
                </div>
            </section>
        </div>
    </div></AppLayout>
</template>
