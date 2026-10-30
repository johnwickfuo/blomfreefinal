<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { PackageX, SlidersHorizontal, Search } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductCard from '@/Components/ProductCard.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import type { Paginated, ProductCardData, Subsidiary } from '@/types/models';

const props = defineProps<{
    subsidiary: Subsidiary;
    products: Paginated<ProductCardData>;
    categories: string[];
    filters: { category: string | null; sort: string; price_min: number | null; price_max: number | null; stock_only: boolean };
}>();
const minPrice = ref(props.filters.price_min?.toString() ?? '');
const maxPrice = ref(props.filters.price_max?.toString() ?? '');
const stockOnly = ref(props.filters.stock_only);
const activeCategory = ref(props.filters.category ?? '');
const sort = ref(props.filters.sort);
const showFilters = ref(false);
const title = props.subsidiary === 'gadgets' ? 'Gadgets & Electronics' : 'Fashion & Accessories';
function apply(): void {
    const params: Record<string, string> = {};
    if (activeCategory.value) params.category = activeCategory.value;
    if (minPrice.value) params.price_min = minPrice.value;
    if (maxPrice.value) params.price_max = maxPrice.value;
    if (sort.value !== 'featured') params.sort = sort.value;
    if (!stockOnly.value) params.stock_only = '0';
    router.get('/' + props.subsidiary, params, { preserveScroll: true, replace: true });
    showFilters.value = false;
}
function setCategory(category: string): void { activeCategory.value = category; apply(); }
function clear(): void { activeCategory.value = ''; minPrice.value='';maxPrice.value='';stockOnly.value = true;sort.value='featured';apply(); }
</script>
<template>
    <SeoMeta :title="title + ' — BLOMFREE Marketplace'" :description="'Shop ' + title.toLowerCase() + ' by category, price and availability on BLOMFREE.'" />
    <AppLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-brand-dark/50"><Link href="/categories" class="hover:text-brand-orange">Categories</Link><span>/</span><span class="text-brand-orange">{{ title }}</span></div>
            <div class="mt-5 flex flex-wrap items-end justify-between gap-4">
                <div><h1 class="text-3xl font-extrabold sm:text-4xl">{{ title }}</h1><p class="mt-2 text-sm text-brand-dark/60">Browse real listings and compare prices before you buy.</p></div>
                <Link href="/search" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-semibold shadow-sm"><Search class="h-4 w-4 text-brand-orange" /> Search</Link>
            </div>
            <div class="mt-7 flex gap-2 overflow-x-auto pb-3" aria-label="Product categories">
                <button type="button" class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold" :class="!activeCategory ? 'bg-brand-orange text-white':'bg-white text-brand-dark/70'" @click="setCategory('')">All</button>
                <button v-for="category in categories" :key="category" type="button" class="shrink-0 rounded-full px-4 py-2 text-sm font-semibold" :class="activeCategory===category?'bg-brand-orange text-white':'bg-white text-brand-dark/70'" @click="setCategory(category)">{{ category }}</button>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold shadow-sm" @click="showFilters=!showFilters"><SlidersHorizontal class="h-4 w-4" /> {{ showFilters ? 'Hide filters' : 'Filters & Price' }}</button>
                <div class="flex items-center gap-2"><label for="catalog-sort" class="text-xs font-semibold text-brand-dark/60">Sort</label>
                    <select id="catalog-sort" v-model="sort" class="max-w-[190px] rounded-xl border-gray-200 bg-white text-sm" @change="apply">
                        <option value="featured">Featured</option><option value="newest">Newest</option><option value="price_asc">Price: low to high</option><option value="price_desc">Price: high to low</option>
                    </select>
                </div>
            </div>
            <form v-if="showFilters" class="mt-4 grid gap-4 rounded-2xl bg-white p-5 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end" @submit.prevent="apply">
                <label class="block text-sm font-semibold">Minimum price (₦)<input v-model="minPrice" type="number" min="0" placeholder="0" class="mt-2 w-full rounded-xl border-gray-300" /></label>
                <label class="block text-sm font-semibold">Maximum price (₦)<input v-model="maxPrice" type="number" min="0" placeholder="Any" class="mt-2 w-full rounded-xl border-gray-300" /></label>
                <label class="flex items-center gap-2 pb-3 text-sm font-semibold"><input v-model="stockOnly" type="checkbox" class="rounded border-gray-300 text-brand-orange focus:ring-brand-orange" /> In stock only</label>
                <div class="flex gap-2"><button type="submit" class="rounded-xl bg-brand-orange px-5 py-3 font-bold text-white">Apply</button><button type="button" class="rounded-xl border border-gray-200 px-4 py-3 text-sm" @click="clear">Clear</button></div>
            </form>
            <p class="mt-7 text-sm text-brand-dark/65"><strong class="text-brand-dark">{{ products.total }}</strong> matching product{{ products.total===1?'':'s' }}</p>
            <div v-if="products.data.length" class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4">
                <ProductCard v-for="product in products.data" :key="product.slug" :product="product" :subsidiary="subsidiary" />
            </div>
            <div v-else class="mt-6 rounded-3xl bg-white p-12 text-center"><PackageX class="mx-auto h-9 w-9 text-brand-orange" /><h2 class="mt-3 text-lg font-bold">No matching products</h2><p class="mt-2 text-sm text-brand-dark/60">Try a different category or price range.</p><button class="mt-4 font-bold text-brand-orange" @click="clear">Clear all filters</button></div>
            <nav v-if="products.last_page>1" aria-label="Product pages" class="mt-8 flex flex-wrap justify-center gap-2">
                <Link v-for="(link,i) in products.links" :key="i" :href="link.url || '#'" :aria-disabled="!link.url" class="rounded-xl px-3 py-2 text-sm" :class="link.active?'bg-brand-orange text-white':'bg-white'">{{ link.label.replace('&laquo;', '«').replace('&raquo;', '»') }}</Link>
            </nav>
        </div>
    </AppLayout>
</template>
