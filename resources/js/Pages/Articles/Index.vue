<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import Section from '@/Components/Section.vue';
defineProps<{ articles: { data: { title: string; slug: string; excerpt: string; category: string; published_at: string; image: string | null }[]; links: { url: string | null; label: string; active: boolean }[] } }>();
</script>
<template>
    <SeoMeta title="Insights | BLOMFREE" description="Guides and updates from BLOMFREE & CO. NIG. LTD." />
    <AppLayout><Section><h1 class="text-4xl font-extrabold">Insights and resources</h1><p class="mt-3 text-brand-dark/70">Guides and company updates from our team.</p><div v-if="articles.data.length" class="mt-10 grid gap-6 md:grid-cols-3"><article v-for="article in articles.data" :key="article.slug" class="overflow-hidden rounded-2xl bg-white shadow-sm"><img v-if="article.image" :src="article.image" :alt="article.title" loading="lazy" class="aspect-[16/10] w-full object-cover" /><div class="p-6"><p class="text-sm text-brand-orange">{{ article.category }} · {{ article.published_at }}</p><h2 class="mt-2 text-xl font-bold">{{ article.title }}</h2><p class="mt-3 text-sm text-brand-dark/70">{{ article.excerpt }}</p><Link :href="'/insights/' + article.slug" class="mt-5 inline-block font-semibold text-brand-orange">Read article →</Link></div></article></div><p v-else class="mt-10 text-brand-dark/60">No articles have been published yet.</p><nav v-if="articles.data.length" aria-label="Article pages" class="mt-8 flex flex-wrap gap-2"><template v-for="(link, i) in articles.links" :key="i"><Link v-if="link.url" :href="link.url" class="rounded-lg px-3 py-2" :class="link.active ? 'bg-brand-orange text-white' : 'bg-white'" v-html="link.label" /></template></nav></Section></AppLayout>
</template>
