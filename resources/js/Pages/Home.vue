<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ChevronRight, ChevronLeft, ArrowRight, Smartphone, Shirt, CarFront, MapPin, PawPrint, Tag, Sparkles, Search, Clock3 } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import MarketplaceCard from '@/Components/MarketplaceCard.vue';
import { divisionLinks } from '@/types/marketplace';
import type { MarketplaceCardData, MarketplaceBanner, MarketplaceSectionData } from '@/types/marketplace';

const props = defineProps<{
    banners: MarketplaceBanner[];
    deals: MarketplaceCardData[];
    featured: MarketplaceCardData[];
    spotlight: MarketplaceCardData[];
    newArrivals: MarketplaceCardData[];
    byDivision: Record<string, MarketplaceCardData[]>;
    sections: MarketplaceSectionData[];
    articles: { title: string; excerpt: string; href: string; image: string | null }[];
}>();
const icons = [Smartphone, Shirt, CarFront, MapPin, PawPrint];
const bannerIndex = ref(0);
const query = ref('');
const currentTime = ref(Date.now());
let ticker: ReturnType<typeof setInterval> | null = null;
onMounted(() => { ticker = setInterval(() => currentTime.value = Date.now(), 1000); });
onBeforeUnmount(() => { if (ticker) clearInterval(ticker); });
const sections = computed(() => props.sections.filter(x => x.enabled));
const activeDealItems = computed(() => props.deals.filter(item => !item.ends_at || new Date(item.ends_at).getTime() > currentTime.value));
const nextDealEnd = computed(() => activeDealItems.value.map(d => d.ends_at ? new Date(d.ends_at).getTime() : Infinity).filter(x => Number.isFinite(x)).sort((a,b) => a-b)[0] ?? null);
const countdown = computed(() => {
    if (!nextDealEnd.value) return null;
    const time = Math.max(0, Math.floor((nextDealEnd.value - currentTime.value) / 1000));
    const h = Math.floor(time / 3600), m = Math.floor((time % 3600) / 60), s = time % 60;
    return [h,m,s].map(n => String(n).padStart(2, '0')).join(':');
});
const selectedBanner = computed(() => props.banners[bannerIndex.value] ?? null);
const visibleSection = (key: string): boolean => sections.value.some(s => s.key === key);
function search(): void { router.get('/search', { q: query.value.trim() }); }
const names: Record<string, string> = { gadgets: 'Gadgets & Electronics', collections: 'Fashion & Accessories', vehicles: 'BLOMWHEELS Vehicles', lands: 'Properties & Land', animals: 'Kennel & Farm' };
function sectionHasData(key: string): boolean {
    if (key === 'deals') return activeDealItems.value.length > 0;
    if (key === 'featured') return props.featured.length > 0;
    if (key === 'spotlight') return props.spotlight.length > 0;
    if (key === 'departments') return Object.values(props.byDivision).some(x => x.length > 0);
    if (key === 'new') return props.newArrivals.length > 0;
    if (key === 'insights') return props.articles.length > 0;
    return false;
}
const anyInventory = computed(() => props.featured.length || props.newArrivals.length || Object.values(props.byDivision).some(x=>x.length));
</script>
<template>
    <SeoMeta title="BLOMFREE Marketplace — Shop Gadgets, Fashion, Vehicles, Land & Animals" description="Discover real products and listings across all five BLOMFREE divisions. Shop gadgets and fashion or enquire about vehicles, properties and animals." />
    <AppLayout>
        <div class="mx-auto max-w-7xl px-3 pb-12 pt-4 sm:px-6 lg:px-8">
            <form class="mb-5 flex items-center rounded-2xl bg-white p-1 ring-1 ring-black/10 md:hidden" role="search" @submit.prevent="search">
                <Search class="ml-3 h-5 w-5 shrink-0 text-brand-dark/55" />
                <label for="homepage-search" class="sr-only">Search the marketplace</label>
                <input id="homepage-search" v-model="query" type="search" placeholder="Search products, cars, properties…" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm text-brand-dark focus:ring-0" />
                <button type="submit" aria-label="Search" class="rounded-xl bg-brand-orange p-3 text-white"><ArrowRight class="h-4 w-4" /></button>
            </form>
            <nav aria-label="Shop by division" class="grid grid-cols-5 gap-1 pb-5 sm:gap-3">
                <Link v-for="(division, i) in divisionLinks" :key="division.key" :href="division.href" class="flex min-w-0 flex-col items-center gap-2 rounded-xl px-1 py-2 transition-colors hover:bg-white">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-brand-orange shadow-sm ring-1 ring-black/5 sm:h-16 sm:w-16"><component :is="icons[i]" class="h-6 w-6 sm:h-7 sm:w-7" /></span>
                    <span class="text-center text-[11px] font-bold leading-tight sm:text-sm">{{ division.shortLabel }}</span>
                </Link>
            </nav>

            <section aria-label="Marketplace promotions">
                <div v-if="selectedBanner" class="relative overflow-hidden rounded-3xl bg-brand-dark text-white">
                    <div class="absolute inset-0" aria-hidden="true"><img v-if="selectedBanner.image" :src="selectedBanner.image" alt="" class="h-full w-full object-cover" fetchpriority="high" /><div class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/90 to-brand-dark/25" /></div>
                    <div class="relative flex min-h-60 flex-col items-start justify-center p-6 sm:min-h-80 sm:p-12">
                        <p v-if="selectedBanner.eyebrow" class="text-xs font-extrabold uppercase tracking-widest text-brand-orange">{{ selectedBanner.eyebrow }}</p>
                        <h1 class="mt-3 max-w-xl text-3xl font-extrabold leading-tight sm:text-5xl">{{ selectedBanner.headline }}</h1>
                        <p v-if="selectedBanner.description" class="mt-3 max-w-lg text-sm text-white/85 sm:text-base">{{ selectedBanner.description }}</p>
                        <Link v-if="selectedBanner.button_url" :href="selectedBanner.button_url" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-brand-orange px-5 py-3 text-sm font-bold text-white">{{ selectedBanner.button_label || 'Explore now' }} <ArrowRight class="h-4 w-4" /></Link>
                    </div>
                    <div v-if="banners.length > 1" class="absolute bottom-3 right-3 flex items-center gap-2 rounded-full bg-black/40 p-1.5">
                        <button type="button" aria-label="Previous promotion" class="rounded-full p-1 text-white" @click="bannerIndex=(bannerIndex-1+banners.length)%banners.length"><ChevronLeft class="h-4 w-4" /></button>
                        <button v-for="(_, i) in banners" :key="i" type="button" :aria-label="'Show promotion '+(i+1)" :aria-pressed="i === bannerIndex" class="h-2 w-2 rounded-full" :class="i===bannerIndex ? 'bg-brand-orange' : 'bg-white/60'" @click="bannerIndex=i" />
                        <button type="button" aria-label="Next promotion" class="rounded-full p-1 text-white" @click="bannerIndex=(bannerIndex+1)%banners.length"><ChevronRight class="h-4 w-4" /></button>
                    </div>
                </div>
                <div v-else class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-dark via-[#382219] to-brand-orange px-6 py-8 text-white sm:px-12 sm:py-12">
                    <div class="relative z-10 max-w-xl"><p class="text-xs font-extrabold uppercase tracking-widest text-orange-300">BLOMFREE Marketplace</p>
                        <h1 class="mt-3 text-3xl font-black leading-tight sm:text-5xl">Find it. Love it. <span class="text-orange-300">Shop it.</span></h1>
                        <p class="mt-3 max-w-md text-sm text-white/80 sm:text-base">Gadgets, fashion, vehicles, properties and animals—all in one place.</p>
                        <Link href="/categories" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-brand-orange px-5 py-3 text-sm font-bold">Explore categories <ArrowRight class="h-4 w-4" /></Link>
                    </div><Sparkles class="absolute -bottom-12 -right-10 h-64 w-64 text-white/10" aria-hidden="true" /></div>
            </section>

            <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold text-brand-dark/65">
                <Link href="/how-it-works" class="rounded-full bg-white px-3 py-2">How shopping works</Link>
                <Link href="/payments" class="rounded-full bg-white px-3 py-2">Payment options</Link>
                <Link href="/track" class="rounded-full bg-white px-3 py-2">Track an order</Link>
                <Link href="/help" class="rounded-full bg-white px-3 py-2">Need help?</Link>
            </div>

            <template v-for="section in sections" :key="section.key">
                <section v-if="sectionHasData(section.key)" :aria-label="section.title" class="mt-9 sm:mt-12">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <div v-if="section.key === 'deals'" class="mb-1 inline-flex items-center gap-1.5 text-xs font-bold uppercase text-red-700"><Tag class="h-4 w-4" /> Real discounts</div>
                            <h2 class="text-xl font-extrabold tracking-tight sm:text-2xl">{{ section.title }}</h2>
                            <p v-if="section.key === 'deals' && countdown" class="mt-1 flex items-center gap-1.5 text-xs text-red-700"><Clock3 class="h-4 w-4" /> Next offer ends in {{ countdown }}</p>
                        </div>
                        <Link v-if="section.key === 'departments'" href="/categories" class="shrink-0 text-xs font-bold text-brand-orange sm:text-sm">All categories →</Link>
                        <Link v-if="section.key === 'insights'" href="/insights" class="shrink-0 text-xs font-bold text-brand-orange sm:text-sm">Read more →</Link>
                        <Link v-if="section.key === 'new'" href="/search" class="shrink-0 text-xs font-bold text-brand-orange sm:text-sm">Explore all →</Link>
                    </div>
                    <div v-if="section.key === 'deals' || section.key === 'featured' || section.key === 'spotlight' || section.key === 'new'"
                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4 xl:grid-cols-5">
                        <MarketplaceCard v-for="item in (section.key === 'deals' ? activeDealItems : section.key === 'featured' ? featured : section.key === 'spotlight' ? spotlight : newArrivals)" :key="item.key" :item="item" />
                    </div>
                    <div v-else-if="section.key === 'departments'" class="space-y-8">
                        <template v-for="division in divisionLinks" :key="division.key">
                            <div v-if="byDivision[division.key]?.length" class="rounded-2xl bg-white/60 p-3 sm:p-5">
                                <div class="mb-4 flex items-center justify-between gap-3"><h3 class="font-extrabold sm:text-lg">{{ names[division.key] }}</h3><Link :href="division.href" class="shrink-0 text-xs font-bold text-brand-orange sm:text-sm">View all <ChevronRight class="inline h-4 w-4" /></Link></div>
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                                    <MarketplaceCard v-for="item in byDivision[division.key].slice(0, 4)" :key="item.key" :item="item" />
                                </div>
                            </div>
                        </template>
                    </div>
                    <div v-else-if="section.key === 'insights'" class="grid gap-4 sm:grid-cols-3">
                        <Link v-for="article in articles" :key="article.href" :href="article.href" class="overflow-hidden rounded-2xl bg-white shadow-sm">
                            <img v-if="article.image" :src="article.image" :alt="article.title" loading="lazy" class="aspect-[16/9] w-full object-cover" />
                            <div class="p-5"><h3 class="font-bold">{{ article.title }}</h3><p class="mt-2 line-clamp-2 text-sm text-brand-dark/60">{{ article.excerpt }}</p><span class="mt-3 inline-flex items-center gap-1 text-sm font-bold text-brand-orange">Read guide <ArrowRight class="h-4 w-4" /></span></div>
                        </Link>
                    </div>
                </section>
            </template>

            <section v-if="!anyInventory" class="mt-9 rounded-3xl bg-white p-8 text-center shadow-sm">
                <h2 class="text-2xl font-extrabold">Explore the BLOMFREE Marketplace</h2>
                <p class="mx-auto mt-3 max-w-lg text-brand-dark/60">Our online inventory is being updated. Browse the divisions or contact our team for availability.</p>
                <Link href="/categories" class="mt-5 inline-flex rounded-xl bg-brand-orange px-6 py-3 font-bold text-white">View all categories</Link>
            </section>
            <div class="mt-12 flex flex-wrap items-center justify-between gap-4 rounded-3xl bg-white p-6 text-sm text-brand-dark/70">
                <p>Want to know more about the people behind BLOMFREE?</p>
                <Link href="/about" class="font-bold text-brand-orange">About our company →</Link>
            </div>
        </div>
    </AppLayout>
</template>
