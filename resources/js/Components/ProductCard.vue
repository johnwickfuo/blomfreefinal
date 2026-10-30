<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ShoppingCart } from 'lucide-vue-next';
import { openCartDrawer } from '@/lib/cart';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import { formatNaira } from '@/lib/format';
import { colorToHex } from '@/lib/colors';
import type { ProductCardData, Subsidiary } from '@/types/models';

const props = defineProps<{
    product: ProductCardData;
    subsidiary: Subsidiary;
}>();

const href = computed(() => `/${props.subsidiary}/${props.product.slug}`);
const adding = ref(false);
function addToCart(): void {
    if (adding.value || !props.product.is_in_stock || props.product.has_variants) return;
    adding.value = true;
    router.post('/cart/add', { cartable_type: 'product', cartable_id: props.product.id, quantity: 1 }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: openCartDrawer,
        onFinish: () => { adding.value = false; },
    });
}

const hasDiscount = computed(
    () =>
        props.product.compare_price !== null &&
        parseFloat(props.product.compare_price) > props.product.display_price,
);

const swatches = computed(() =>
    props.product.colors.slice(0, 5).map((name) => ({
        name,
        hex: colorToHex(name),
    })),
);
</script>

<template>
    <Card class="flex flex-col overflow-hidden">
        <Link :href="href" class="block">
            <div class="aspect-square w-full bg-brand-dark/5">
                <img
                    v-if="product.cover_url"
                    :src="product.cover_url"
                    :alt="product.name"
                    loading="lazy"
                    decoding="async"
                    class="h-full w-full object-cover"
                />
            </div>
        </Link>

        <div class="flex flex-1 flex-col gap-2 p-5">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-brand-dark/45">
                    {{ product.category }}
                </p>
                <Badge :variant="product.is_in_stock ? 'success' : 'neutral'">
                    {{ product.is_in_stock ? 'In Stock' : 'Out of Stock' }}
                </Badge>
            </div>

            <Link
                :href="href"
                class="font-bold leading-snug tracking-tight transition-colors hover:text-brand-orange"
            >
                {{ product.name }}
            </Link>

            <div
                v-if="swatches.length"
                class="flex items-center gap-1.5"
                aria-label="Available colours"
            >
                <span
                    v-for="swatch in swatches"
                    :key="swatch.name"
                    :title="swatch.name"
                    class="h-4 w-4 rounded-full ring-1 ring-black/10"
                    :class="swatch.hex === null ? 'bg-brand-dark/20' : ''"
                    :style="swatch.hex ? { backgroundColor: swatch.hex } : undefined"
                />
            </div>

            <div class="mt-auto flex items-baseline gap-2 pt-2">
                <span class="text-lg font-extrabold tracking-tight text-brand-orange">
                    {{ formatNaira(product.display_price) }}
                </span>
                <span
                    v-if="hasDiscount"
                    class="text-sm font-medium text-brand-dark/40 line-through"
                >
                    {{ formatNaira(product.compare_price) }}
                </span>
            </div>
            <button v-if="product.is_in_stock && !product.has_variants" type="button" :disabled="adding"
                class="mt-3 inline-flex items-center justify-center gap-2 rounded-xl bg-brand-orange px-4 py-2.5 text-xs font-bold text-white disabled:opacity-50 sm:text-sm"
                @click="addToCart"><ShoppingCart class="h-4 w-4" />{{ adding ? 'Adding…' : 'Add to cart' }}</button>
            <Link v-else-if="product.has_variants && product.is_in_stock" :href="href"
                class="mt-3 rounded-xl bg-brand-dark px-4 py-2.5 text-center text-xs font-bold text-white sm:text-sm">Choose options</Link>
        </div>
    </Card>
</template>
