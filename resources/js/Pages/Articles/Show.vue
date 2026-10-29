<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import Section from '@/Components/Section.vue';
defineProps<{ article: { title: string; excerpt: string; body: string; category: string; published_at: string; seo_title: string | null; seo_description: string | null; image: string | null } }>();
</script>
<template>
    <SeoMeta :title="article.seo_title || article.title" :description="article.seo_description || article.excerpt" :image="article.image || undefined" />
    <AppLayout><Section width="narrow"><Link href="/insights" class="text-sm font-semibold text-brand-orange">← All insights</Link><p class="mt-8 text-sm text-brand-orange">{{ article.category }} · {{ article.published_at }}</p><h1 class="mt-3 text-4xl font-extrabold leading-tight">{{ article.title }}</h1><p class="mt-5 text-lg text-brand-dark/70">{{ article.excerpt }}</p><img v-if="article.image" :src="article.image" :alt="article.title" class="mt-8 aspect-[16/9] w-full rounded-2xl object-cover" /><div class="mt-10 whitespace-pre-wrap leading-8 text-brand-dark/85">{{ article.body.replace(/<[^>]*>/g, '') }}</div></Section></AppLayout>
</template>
