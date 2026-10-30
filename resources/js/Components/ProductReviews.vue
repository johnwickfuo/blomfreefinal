<script setup lang="ts">
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Star } from 'lucide-vue-next';

interface Review { id: number; name: string; rating: number; title: string; body: string; date: string }
interface MyReview { rating: number; display_name: string; title: string; body: string; status: string }
const props = defineProps<{
    productSlug: string; reviews: Review[]; summary: { count: number; average: number };
    canReview: boolean; myReview: MyReview | null;
}>();
const form = useForm({
    rating: props.myReview?.rating ?? 5,
    display_name: props.myReview?.display_name ?? '',
    title: props.myReview?.title ?? '',
    body: props.myReview?.body ?? '',
});
const label = computed(() => props.summary.count ? props.summary.average.toFixed(1) + ' / 5' : 'No reviews yet');
function submit(): void { form.post('/shop/products/' + encodeURIComponent(props.productSlug) + '/reviews', { preserveScroll: true }); }
</script>
<template>
    <section class="mt-12 border-t border-black/10 pt-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-extrabold">Customer reviews</h2>
                <p class="mt-2 text-sm text-brand-dark/65">{{ label }} · {{ summary.count }} approved verified-purchase review{{ summary.count === 1 ? '' : 's' }}</p>
            </div>
        </div>
        <div v-if="reviews.length" class="mt-6 grid gap-4 md:grid-cols-2">
            <article v-for="review in reviews" :key="review.id" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-black/5">
                <div class="flex items-center gap-2"><span class="font-bold">{{ review.name }}</span><span class="rounded-full bg-green-50 px-2 py-1 text-xs text-green-700">Verified buyer</span></div>
                <p class="mt-2 text-brand-orange" :aria-label="review.rating + ' out of 5 stars'">{{ '★'.repeat(review.rating) }}{{ '☆'.repeat(5 - review.rating) }}</p>
                <h3 class="mt-2 font-bold">{{ review.title }}</h3><p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-brand-dark/70">{{ review.body }}</p>
                <p class="mt-3 text-xs text-brand-dark/40">{{ review.date }}</p>
            </article>
        </div>
        <p v-else class="mt-5 text-sm text-brand-dark/60">No approved reviews have been published for this product yet.</p>
        <div v-if="canReview" class="mt-8 max-w-2xl rounded-2xl bg-white p-6 ring-1 ring-black/5">
            <h3 class="text-xl font-bold">{{ myReview ? 'Update your review' : 'Share your experience' }}</h3>
            <p class="mt-1 text-sm text-brand-dark/60">Only customers with a paid purchase can review this item. Reviews are moderated before publication.</p>
            <p v-if="myReview" class="mt-2 text-xs font-semibold text-brand-orange">Your review status: {{ myReview.status }}</p>
            <form class="mt-5 space-y-4" @submit.prevent="submit">
                <div><label class="block text-sm font-semibold">Rating</label><div class="mt-2 flex gap-2">
                    <button v-for="score in 5" :key="score" type="button" :aria-label="score + ' stars'" :aria-pressed="form.rating === score" @click="form.rating = score">
                        <Star class="h-7 w-7" :class="score <= form.rating ? 'fill-brand-orange text-brand-orange' : 'text-gray-300'" />
                    </button>
                </div><p class="text-sm text-red-600">{{ form.errors.rating }}</p></div>
                <label class="block text-sm font-semibold">Display name<input v-model="form.display_name" required minlength="2" maxlength="60" class="mt-1 w-full rounded-xl border-gray-300" /><span class="block text-sm text-red-600">{{ form.errors.display_name }}</span></label>
                <label class="block text-sm font-semibold">Review title<input v-model="form.title" required minlength="3" maxlength="120" class="mt-1 w-full rounded-xl border-gray-300" /><span class="block text-sm text-red-600">{{ form.errors.title }}</span></label>
                <label class="block text-sm font-semibold">Your experience<textarea v-model="form.body" required minlength="15" maxlength="2000" rows="4" class="mt-1 w-full rounded-xl border-gray-300" /><span class="block text-sm text-red-600">{{ form.errors.body }}</span></label>
                <button type="submit" :disabled="form.processing" class="rounded-xl bg-brand-orange px-6 py-3 font-semibold text-white disabled:opacity-50">{{ form.processing ? 'Submitting…' : 'Submit for approval' }}</button>
            </form>
        </div>
        <p v-else class="mt-7 text-sm text-brand-dark/60">Purchased this product? <Link href="/login" class="font-semibold text-brand-orange underline">Sign in</Link> with your verified email to leave a review.</p>
    </section>
</template>
