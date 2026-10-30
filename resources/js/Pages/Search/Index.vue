<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Search, ArrowRight, SlidersHorizontal } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import { formatNaira } from '@/lib/format';

type GroupKey = 'properties' | 'animals' | 'collections' | 'gadgets' | 'vehicles' | 'articles';
interface SearchItem { id: number; title: string; subtitle: string | null; image: string | null; price: number | null; href: string }
const props = defineProps<{
    query: string; activeType: string; groups: Record<GroupKey, SearchItem[]>; hasSearched: boolean;
}>();
const q = ref(props.query);
const tabs: { value: string; label: string }[] = [
    { value: 'all', label: 'All results' }, { value: 'properties', label: 'Real Estate' },
    { value: 'animals', label: 'Kennel & Farm' }, { value: 'collections', label: 'Collections' },
    { value: 'gadgets', label: 'Gadgets' }, { value: 'vehicles', label: 'BLOMWHEELS' },
    { value: 'articles', label: 'Insights' },
];
const sections = computed(() => tabs.filter(t => t.value !== 'all' &&
    (props.activeType === 'all' || props.activeType === t.value)));
const total = computed(() => Object.values(props.groups).reduce((sum, items) => sum + items.length, 0));
function search(type = props.activeType): void {
    router.get('/search', { q: q.value.trim(), type }, { preserveScroll: false, replace: true });
}
</script>
<template>
    <SeoMeta title="Search BLOMFREE — All Five Divisions" description="Find real estate, animals, fashion, gadgets, automobiles and articles across BLOMFREE." />
    <AppLayout>
        <div class="bg-brand-dark px-4 py-14 text-white sm:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="flex items-center gap-2 font-bold uppercase tracking-widest text-brand-orange"><Search class="h-5 w-5" /> Site-wide search</div>
                <h1 class="mt-4 text-4xl font-extrabold sm:text-5xl">Explore all of <span class="text-brand-orange">BLOMFREE</span></h1>
                <p class="mt-4 max-w-2xl text-white/70">Search listings and helpful articles across our businesses.</p>
                <form class="mt-8 flex max-w-3xl flex-col gap-3 sm:flex-row" @submit.prevent="search()">
                    <label for="global-search" class="sr-only">Search across BLOMFREE</label>
                    <input id="global-search" v-model="q" type="search" minlength="2" maxlength="80"
                        placeholder="Search land, products, vehicles, animals…" class="min-w-0 flex-1 rounded-xl border-0 px-5 py-3 text-brand-dark" />
                    <button type="submit" class="rounded-xl bg-brand-orange px-8 py-3 font-semibold text-white">Search</button>
                </form>
            </div>
        </div>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <nav aria-label="Search categories" class="flex flex-wrap gap-2">
                <button v-for="tab in tabs" :key="tab.value" type="button" class="rounded-full border px-4 py-2 text-sm font-semibold"
                    :class="activeType === tab.value ? 'border-brand-orange bg-brand-orange text-white' : 'border-gray-200 bg-white hover:border-brand-orange'"
                    @click="search(tab.value)">{{ tab.label }}</button>
            </nav>
            <p v-if="!hasSearched" class="mt-12 rounded-3xl bg-white p-10 text-center text-brand-dark/70">Enter at least two characters to discover listings.</p>
            <p v-else-if="!total" class="mt-12 rounded-3xl bg-white p-10 text-center text-brand-dark/70">No matching published listings found. Try another keyword or category.</p>
            <template v-else>
                <p class="mt-8 text-sm text-brand-dark/65">Showing {{ total }} matching result{{ total === 1 ? '' : 's' }}{{ activeType === 'all' ? ' across our divisions' : '' }} (up to 8 per division).</p>
                <section v-for="section in sections" :key="section.value" class="mt-10">
                    <template v-if="groups[section.value as GroupKey].length">
                        <h2 class="mb-5 flex items-center gap-2 text-2xl font-extrabold"><SlidersHorizontal class="h-5 w-5 text-brand-orange" /> {{ section.label }}</h2>
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <Link v-for="item in groups[section.value as GroupKey]" :key="item.id" :href="item.href"
                                class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-black/5 hover:shadow-lg">
                                <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                                    <img v-if="item.image" :src="item.image" :alt="item.title" loading="lazy" class="h-full w-full object-cover transition-transform group-hover:scale-105" />
                                    <div v-else class="flex h-full items-center justify-center text-sm text-gray-500">No photo available</div>
                                </div>
                                <div class="flex flex-1 flex-col gap-2 p-5">
                                    <h3 class="font-bold group-hover:text-brand-orange">{{ item.title }}</h3>
                                    <p class="text-sm text-brand-dark/60">{{ item.subtitle }}</p>
                                    <p v-if="item.price !== null" class="mt-auto pt-2 font-bold text-brand-orange">{{ formatNaira(item.price) }}</p>
                                    <span class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-brand-orange">View details <ArrowRight class="h-4 w-4" /></span>
                                </div>
                            </Link>
                        </div>
                    </template>
                </section>
            </template>
        </div>
    </AppLayout>
</template>
