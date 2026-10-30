<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Phone, PackageSearch, CreditCard, CalendarCheck, HelpCircle, BookOpen } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
const links = [
    { title:'Track an order', text:'Check the status of an order using its reference.', href:'/track',icon:PackageSearch },
    { title:'Payment options', text:'Learn about checkout and installments.',href:'/payments',icon:CreditCard },
    { title:'How shopping works', text:'Purchases, enquiries and inspections explained.',href:'/how-it-works',icon:BookOpen },
    { title:'Customer account', text:'Access your orders, installment plans and profile.',href:'/my',icon:CalendarCheck },
    { title:'Contact support', text:'Ask a question or contact the BLOMFREE team.',href:'/contact',icon:Phone },
];
</script>
<template><SeoMeta title="Help Centre — BLOMFREE" description="Find order tracking, payment options, customer help and contact links." />
<AppLayout><div class="mx-auto max-w-5xl px-4 py-12 sm:px-6"><p class="font-bold uppercase tracking-widest text-brand-orange">Customer support</p>
<h1 class="mt-3 text-4xl font-extrabold">How can we help?</h1>
<p class="mt-3 text-brand-dark/65">Choose a topic to find the right page or contact our team.</p>
<div class="mt-8 grid gap-4 sm:grid-cols-2">
<Link v-for="link in links" :key="link.href" :href="link.href" class="flex items-start gap-3 rounded-2xl bg-white p-6 shadow-sm hover:ring-1 hover:ring-brand-orange">
    <component :is="link.icon" class="h-6 w-6 shrink-0 text-brand-orange" /><div class="flex-1"><h2 class="font-bold">{{ link.title }}</h2><p class="mt-1 text-sm text-brand-dark/65">{{ link.text }}</p></div><ArrowRight class="h-4 w-4 shrink-0 text-brand-orange" />
</Link></div></div></AppLayout></template>
