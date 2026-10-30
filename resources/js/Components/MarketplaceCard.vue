<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Heart, ShoppingCart, ArrowUpRight } from 'lucide-vue-next';
import SaveListingButton from '@/Components/SaveListingButton.vue';
import { openCartDrawer } from '@/lib/cart';
import { formatNaira } from '@/lib/format';
import type { MarketplaceCardData } from '@/types/marketplace';

const props = defineProps<{ item: MarketplaceCardData; eager?: boolean }>();
const isAdding = ref(false);
const saveType = () => props.item.type === 'gadgets' || props.item.type === 'collections' ? 'products'
    : props.item.type === 'vehicles' ? 'vehicles' : props.item.type;
function addToCart(): void {
    if (!props.item.can_add || isAdding.value) return;
    isAdding.value = true;
    router.post('/cart/add', {
        cartable_type: 'product', cartable_id: props.item.id, quantity: 1,
    }, { preserveScroll: true, preserveState: true,
        onSuccess: openCartDrawer,
        onFinish: () => { isAdding.value = false; },
    });
}
</script>
<template>
    <article class="group flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border border-black/5 bg-white shadow-sm transition-shadow hover:shadow-lg">
        <div class="relative aspect-[4/4] w-full overflow-hidden bg-gray-100 sm:aspect-[5/4]">
            <Link :href="item.href" class="block h-full w-full">
                <img v-if="item.image" :src="item.image" :alt="item.title" :loading="eager ? 'eager' : 'lazy'" :fetchpriority="eager ? 'high' : 'auto'"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                <div v-else class="flex h-full items-center justify-center bg-gradient-to-br from-brand-cream to-gray-200 px-4 text-center text-xs font-semibold text-brand-dark/40">Image not available</div>
            </Link>
            <span class="absolute left-2 top-2 rounded-lg bg-brand-dark/90 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white sm:text-xs">{{ item.type === 'vehicles' ? 'Automobile' : item.type === 'lands' ? 'Property' : item.type === 'collections' ? 'Fashion' : item.type }}</span>
            <span v-if="item.discount_percent && item.discount_percent > 0" class="absolute bottom-2 left-2 rounded-lg bg-red-600 px-2 py-1 text-xs font-bold text-white">-{{ item.discount_percent }}%</span>
            <div class="absolute right-2 top-2"><SaveListingButton :type="saveType()" :id="item.id" compact /></div>
        </div>
        <div class="flex flex-1 flex-col gap-1.5 p-3 sm:p-4">
            <Link :href="item.href" class="line-clamp-2 min-h-10 text-sm font-bold leading-snug text-brand-dark hover:text-brand-orange sm:text-base">{{ item.title }}</Link>
            <p v-if="item.subtitle" class="truncate text-xs text-brand-dark/55">{{ item.subtitle }}</p>
            <div class="mt-auto pt-2">
                <p class="text-base font-extrabold text-brand-orange sm:text-lg">{{ formatNaira(item.price) }}</p>
                <p v-if="item.original_price" class="text-xs text-brand-dark/45 line-through">{{ formatNaira(item.original_price) }}</p>
                <p v-if="item.stock !== null" class="mt-1 text-[11px] text-brand-dark/55">{{ item.stock }} in stock</p>
            </div>
            <button v-if="item.can_add" type="button" :disabled="isAdding" class="mt-2 flex items-center justify-center gap-1.5 rounded-xl bg-brand-orange px-2 py-2.5 text-xs font-bold text-white transition-colors hover:bg-brand-orangeDark disabled:opacity-50 sm:text-sm"
                @click="addToCart"><ShoppingCart class="h-4 w-4" /> {{ isAdding ? 'Adding…' : 'Add to cart' }}</button>
            <Link v-else :href="item.href" class="mt-2 flex items-center justify-center gap-1.5 rounded-xl bg-brand-dark px-2 py-2.5 text-xs font-bold text-white hover:bg-brand-orange sm:text-sm">{{ item.action }} <ArrowUpRight class="h-4 w-4" /></Link>
        </div>
    </article>
</template>
