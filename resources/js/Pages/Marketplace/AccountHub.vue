<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { UserRound, ShoppingBag, Heart, PackageSearch, Wallet, CalendarCheck, CircleHelp, LogIn, UserPlus, ArrowRight, Settings } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
const page = usePage();
const user = computed(() => (page.props.auth as {user?: {name:string}|null} | undefined)?.user ?? null);
const items = [
    { label:'My account dashboard', text:'Account overview and recent activity',href:'/account', icon:UserRound, secure:true },
    { label:'My orders', text:'View purchases and order history',href:'/account/orders',icon:ShoppingBag,secure:true },
    { label:'Track an order', text:'Look up an order reference',href:'/track',icon:PackageSearch,secure:false },
    { label:'Saved listings', text:'See items bookmarked on this device',href:'/saved',icon:Heart,secure:false },
    { label:'Installment plans', text:'Review eligible payment plans',href:'/account/installments',icon:Wallet,secure:true },
    { label:'Book an inspection', text:'Browse property, animal and vehicle listings',href:'/categories',icon:CalendarCheck,secure:false },
    { label:'Profile & security', text:'Manage your account information',href:'/account/profile',icon:Settings,secure:true },
    { label:'Help & support', text:'Contact information and help topics',href:'/help',icon:CircleHelp,secure:false },
];
</script>
<template><SeoMeta title="My Account — BLOMFREE" description="Access orders, saved items, tracking, installments and customer support." />
<AppLayout><div class="mx-auto max-w-5xl px-4 py-10 sm:px-6"><p class="text-sm font-bold uppercase tracking-widest text-brand-orange">Your BLOMFREE</p>
<h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">{{ user ? 'Welcome, ' + user.name : 'My account' }}</h1>
<p class="mt-3 text-brand-dark/65">Everything you need to manage your shopping experience.</p>
<div v-if="!user" class="mt-6 flex gap-3"><Link href="/login" class="inline-flex items-center gap-2 rounded-xl bg-brand-orange px-6 py-3 font-bold text-white"><LogIn class="h-4 w-4" /> Sign in</Link><Link href="/register" class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 font-bold"><UserPlus class="h-4 w-4" /> Register</Link></div>
<div class="mt-8 grid gap-3 sm:grid-cols-2"><Link v-for="item in items" :key="item.href" :href="item.secure && !user ? '/login' : item.href"
    class="flex items-center gap-3 rounded-2xl bg-white p-5 shadow-sm transition-shadow hover:shadow-lg">
    <component :is="item.icon" class="h-6 w-6 shrink-0 text-brand-orange" /><div class="flex-1"><h2 class="font-bold">{{ item.label }}</h2><p class="mt-1 text-xs text-brand-dark/60">{{ item.text }}</p></div><ArrowRight class="h-4 w-4 shrink-0 text-brand-orange" />
</Link></div>
<div class="mt-10 flex flex-wrap gap-5 text-sm font-semibold"><Link href="/about">About BLOMFREE</Link><Link href="/how-it-works">How it works</Link><Link href="/payments">Payment options</Link><Link href="/contact">Contact us</Link></div>
</div></AppLayout></template>
