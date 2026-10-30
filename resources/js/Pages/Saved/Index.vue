<script setup lang="ts">
import { onMounted, onBeforeUnmount, computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Heart, ArrowRight } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import SaveListingButton from '@/Components/SaveListingButton.vue';
import { allSavedUrl } from '@/lib/savedListings';
import type { SavedType } from '@/lib/savedListings';
import { formatNaira } from '@/lib/format';

interface Item { id: number; title: string; meta: string | null; image: string | null; price: number; href: string }
const props = defineProps<{ groups: Record<SavedType, Item[]> }>();
const types: { type: SavedType; title: string }[] = [
    { type: 'lands', title: 'Real Estate' }, { type: 'animals', title: 'Kennel & Farm' },
    { type: 'products', title: 'Fashion & Gadgets' }, { type: 'vehicles', title: 'BLOMWHEELS' },
];
const total = computed(() => Object.values(props.groups).reduce((n, list) => n + list.length, 0));
const firstLoad = ref(true);
function refresh(): void {
    const next = allSavedUrl();
    if (firstLoad.value) {
        firstLoad.value = false;
        if (typeof window !== 'undefined' && window.location.pathname + window.location.search === next) return;
    }
    router.visit(next, { preserveScroll: true, preserveState: false });
}
onMounted(() => {
    refresh();
    window.addEventListener('blomfree-saved-changed', refresh);
    window.addEventListener('blomwheels-list-changed', refresh);
});
onBeforeUnmount(() => {
    window.removeEventListener('blomfree-saved-changed', refresh);
    window.removeEventListener('blomwheels-list-changed', refresh);
});
</script>
<template>
    <SeoMeta title="Your Saved Listings — BLOMFREE" description="See listings saved on this device from all BLOMFREE divisions." />
    <AppLayout>
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <span class="flex items-center gap-2 text-sm font-bold uppercase text-brand-orange"><Heart class="h-5 w-5" /> Your shortlist</span>
            <h1 class="mt-3 text-3xl font-extrabold sm:text-4xl">Saved listings</h1>
            <p class="mt-3 text-brand-dark/65">Keep track of listings you're interested in, across all five businesses. Saved on this device—no account required.</p>
            <div v-if="total === 0" class="mt-10 rounded-3xl border border-dashed border-gray-300 bg-white p-12 text-center">
                <Heart class="mx-auto h-10 w-10 text-brand-orange/70" />
                <h2 class="mt-4 text-xl font-bold">Your shortlist is empty</h2>
                <p class="mt-2 text-sm text-brand-dark/60">Explore BLOMFREE and save listings you want to return to.</p>
                <Link href="/search" class="mt-4 inline-flex rounded-xl bg-brand-orange px-6 py-3 font-semibold text-white">Explore listings</Link>
            </div>
            <section v-for="section in types" :key="section.type" class="mt-10">
                <template v-if="groups[section.type].length">
                    <h2 class="mb-5 text-2xl font-extrabold">{{ section.title }}</h2>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <article v-for="item in groups[section.type]" :key="item.id" class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
                            <Link :href="item.href" class="aspect-[16/10] bg-gray-100">
                                <img v-if="item.image" :src="item.image" :alt="item.title" loading="lazy" class="h-full w-full object-cover" />
                            </Link>
                            <div class="flex flex-1 flex-col gap-2 p-5">
                                <Link :href="item.href" class="font-bold hover:text-brand-orange">{{ item.title }}</Link>
                                <p class="text-sm text-brand-dark/60">{{ item.meta }}</p>
                                <p class="mt-auto pt-2 font-bold text-brand-orange">{{ formatNaira(item.price) }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <SaveListingButton :type="section.type" :id="item.id" />
                                    <Link :href="item.href" aria-label="View listing" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-orange">Details <ArrowRight class="h-4 w-4" /></Link>
                                </div>
                            </div>
                        </article>
                    </div>
                </template>
            </section>
        </div>
    </AppLayout>
</template>
