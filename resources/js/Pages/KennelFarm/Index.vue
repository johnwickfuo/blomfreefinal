<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dna, ShieldCheck, Syringe, LifeBuoy, HeartHandshake } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Section.vue';
import Badge from '@/Components/Badge.vue';
import AnimalCard from '@/Components/AnimalCard.vue';
import CtaBand from '@/Components/CtaBand.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import type { AnimalsByCategory, TabCategory } from '@/types/models';

const props = defineProps<{
    animalsByCategory: AnimalsByCategory;
    filters?: { category?: TabCategory; price_min?: number; price_max?: number };
}>();

interface Tab {
    key: TabCategory;
    label: string;
    hash: string;
}

const tabs: Tab[] = [
    { key: 'dog', label: 'Dogs', hash: 'dogs' },
    { key: 'cat', label: 'Cats', hash: 'cats' },
    { key: 'rabbit', label: 'Rabbits', hash: 'rabbits' },
    { key: 'grasscutter', label: 'Grasscutters', hash: 'grasscutters' },
];

const activeTab = ref<TabCategory>(props.filters?.category ?? 'dog');
const minPrice = ref(props.filters?.price_min?.toString() ?? '');
const maxPrice = ref(props.filters?.price_max?.toString() ?? '');
function filterPrices(): void {
    const p: Record<string,string> = { category: activeTab.value };
    if (minPrice.value) p.price_min = minPrice.value;
    if (maxPrice.value) p.price_max = maxPrice.value;
    router.get('/kennel-farm', p, { preserveScroll: true });
}

onMounted(() => {
    const hash = window.location.hash.replace('#', '');
    const queryCategory = new URLSearchParams(window.location.search).get('category');
    if (tabs.some(t => t.key === queryCategory)) activeTab.value = queryCategory as TabCategory;
    const matched = tabs.find((tab) => tab.hash === hash);
    if (matched) {
        activeTab.value = matched.key;
    }
});

function selectTab(tab: Tab): void {
    activeTab.value = tab.key;
    history.replaceState(null, '', `#${tab.hash}`);
    if (minPrice.value || maxPrice.value) filterPrices();
}

const activeAnimals = computed(() => props.animalsByCategory[activeTab.value] ?? []);

const reasons = [
    {
        icon: Dna,
        title: 'Review breed information',
        body: 'Check breed and parent details for the individual listing when provided.',
    },
    {
        icon: ShieldCheck,
        title: 'Ask about health records',
        body: 'Confirm veterinary checks and documentation for the animal you are considering.',
    },
    {
        icon: Syringe,
        title: 'Check vaccination details',
        body: 'Review available vaccination and deworming information and ask about missing records.',
    },
    {
        icon: LifeBuoy,
        title: 'Plan responsible care',
        body: 'Understand feeding, housing and veterinary needs before arranging delivery.',
    },
    {
        icon: HeartHandshake,
        title: 'Arrange an inspection',
        body: 'Use available inspection options to see the animal and verify its condition.',
    },
];
</script>

<template>
    <SeoMeta
        title="BLOMFREE Kennel & Farm — Premium breeds"
        description="Dogs, cats, rabbits and grasscutters — imported and locally bred to a high standard, health-checked and ethically kept."
    />

    <AppLayout>
        <div class="bg-brand-dark px-4 py-8 text-white sm:py-12">
            <div class="mx-auto max-w-7xl">
                <p class="text-xs font-bold uppercase tracking-widest text-brand-orange">BLOMFREE Kennel & Farm</p>
                <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">Browse available animals</h1>
                <p class="mt-2 max-w-2xl text-sm text-white/70 sm:text-base">Explore listed dogs, cats, rabbits and livestock. Review their details and ask about health records or inspection arrangements.</p>
            </div>
        </div>

        <Section>
            <form class="mb-6 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4" @submit.prevent="filterPrices">
                <label class="text-xs font-bold">Min price (₦)<input v-model="minPrice" type="number" min="0" class="mt-1 block w-36 rounded-xl border-gray-300 text-sm" /></label>
                <label class="text-xs font-bold">Max price (₦)<input v-model="maxPrice" type="number" min="0" class="mt-1 block w-36 rounded-xl border-gray-300 text-sm" /></label>
                <button type="submit" class="rounded-xl bg-brand-orange px-4 py-2.5 text-sm font-bold text-white">Filter prices</button>
                <button type="button" class="rounded-xl px-4 py-2 text-sm font-semibold" @click="minPrice='';maxPrice='';filterPrices()">Clear</button>
            </form>
            <!-- Category tabs -->
            <div
                class="flex flex-wrap gap-2 border-b border-brand-dark/10 pb-1"
                role="tablist"
            >
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === tab.key"
                    class="rounded-t-xl border-b-2 px-4 py-2.5 text-sm font-bold tracking-tight transition-colors"
                    :class="
                        activeTab === tab.key
                            ? 'border-brand-orange text-brand-orange'
                            : 'border-transparent text-brand-dark/55 hover:text-brand-dark'
                    "
                    @click="selectTab(tab)"
                >
                    {{ tab.label }}
                    <span class="ml-1 text-xs font-semibold text-brand-dark/35">
                        {{ animalsByCategory[tab.key]?.length ?? 0 }}
                    </span>
                </button>
            </div>

            <!-- Grid -->
            <div
                v-if="activeAnimals.length"
                class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <AnimalCard
                    v-for="animal in activeAnimals"
                    :key="animal.id"
                    :animal="animal"
                />
            </div>
            <div
                v-else
                class="mt-8 flex flex-col items-center justify-center gap-2 rounded-3xl border border-dashed border-brand-dark/15 bg-white/60 px-6 py-16 text-center"
            >
                <p class="text-lg font-bold tracking-tight">
                    Nothing listed here yet
                </p>
                <p class="max-w-sm text-sm text-brand-dark/55">
                    Browse another category or
                    contact us about availability.
                </p>
            </div>
        </Section>

        <!-- Why choose us -->
        <section class="bg-brand-dark text-white">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
                <div class="max-w-2xl">
                    <span
                        class="inline-flex items-center rounded-full bg-brand-orange/15 px-2.5 py-0.5 text-xs font-semibold text-brand-orange"
                    >
                        Why Choose Us
                    </span>
                    <h2
                        class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                    >
                        Important checks before buying an animal
                    </h2>
                </div>
                <div
                    class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="reason in reasons"
                        :key="reason.title"
                        class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10"
                    >
                        <span
                            class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-orange/15 text-brand-orange"
                        >
                            <component :is="reason.icon" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 text-base font-bold tracking-tight">
                            {{ reason.title }}
                        </h3>
                        <p
                            class="mt-1.5 text-sm leading-relaxed text-white/60"
                        >
                            {{ reason.body }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Inquiry CTA -->
        <CtaBand
            title="Looking for a breed we haven't listed?"
            subtitle="Tell us what you need — dogs, cats, rabbits or grasscutters — and our team will source it for you."
        />
    </AppLayout>
</template>
