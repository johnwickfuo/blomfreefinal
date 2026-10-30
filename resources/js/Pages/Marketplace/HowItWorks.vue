<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Search, ShoppingCart, CalendarCheck, PackageCheck, ArrowRight } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
const steps = [
    { name: 'Explore real listings', description:'Search across gadgets, fashion, vehicles, land and animals. Filter products by category, price and availability.', icon:Search },
    { name: 'Review your options', description:'Check real photos, prices, specifications and stock before deciding. Save listings for later.', icon: ShoppingCart },
    { name: 'Buy or enquire', description:'Eligible items can be placed in the cart and paid for online. Vehicle, land and inspection-based listings support enquiries or viewing requests.', icon:CalendarCheck },
    { name: 'Track what happens next', description:'Track orders using your reference. Your account provides order history and installment-plan information where applicable.', icon: PackageCheck },
];
</script>
<template>
    <SeoMeta title="How Shopping Works — BLOMFREE" description="Understand how to browse, buy, enquire, book inspections and track orders at BLOMFREE." />
    <AppLayout><div class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
        <p class="font-bold uppercase tracking-widest text-brand-orange">Customer guide</p>
        <h1 class="mt-3 text-4xl font-extrabold">How BLOMFREE shopping works</h1>
        <p class="mt-4 max-w-3xl text-brand-dark/70">All five divisions are available to explore, but not every listing has the same purchasing process. Here's what to expect.</p>
        <div class="mt-9 grid gap-5 sm:grid-cols-2"><div v-for="(step,i) in steps" :key="step.name" class="rounded-3xl bg-white p-7 shadow-sm">
            <div class="flex items-center gap-3"><span class="font-extrabold text-brand-orange">0{{ i+1 }}</span><component :is="step.icon" class="h-7 w-7 text-brand-orange" /></div>
            <h2 class="mt-4 text-xl font-bold">{{ step.name }}</h2><p class="mt-3 text-sm leading-relaxed text-brand-dark/65">{{ step.description }}</p>
        </div></div>
        <div class="mt-10 flex flex-wrap gap-3"><Link href="/categories" class="rounded-xl bg-brand-orange px-5 py-3 font-bold text-white">Browse categories <ArrowRight class="inline h-4 w-4" /></Link><Link href="/payments" class="rounded-xl border border-gray-300 px-5 py-3 font-bold">Payment information</Link></div>
    </div></AppLayout>
</template>
