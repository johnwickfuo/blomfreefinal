<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
</script>
<template><SeoMeta title="Payment Options — BLOMFREE" description="Learn about eligible online checkout, installment plans and enquiry-first vehicle/property transactions." />
<AppLayout><div class="mx-auto max-w-4xl px-4 py-12 sm:px-6"><p class="font-bold uppercase text-brand-orange">Shopping help</p>
    <h1 class="mt-3 text-4xl font-extrabold">Payments and installments</h1>
    <div class="mt-8 space-y-5">
        <section class="rounded-3xl bg-white p-7 shadow-sm"><h2 class="text-xl font-bold">Paying for eligible products</h2>
            <p class="mt-3 leading-relaxed text-brand-dark/70">Eligible gadgets, fashion and selected animal listings can be added to the shopping cart. Checkout presents supported payment gateways and the amount due. Review your order before paying.</p>
            <Link href="/cart" class="mt-4 inline-block font-bold text-brand-orange">View your cart →</Link></section>
        <section class="rounded-3xl bg-white p-7 shadow-sm"><h2 class="text-xl font-bold">Installment payments</h2>
            <p class="mt-3 leading-relaxed text-brand-dark/70">Some land and gadget listings support installment plans when enabled. The listing and application pages show eligibility, applicable down payments, durations and terms. Plans are not guaranteed for every item.</p>
            <Link href="/installments" class="mt-4 inline-block font-bold text-brand-orange">How installments work →</Link></section>
        <section class="rounded-3xl bg-white p-7 shadow-sm"><h2 class="text-xl font-bold">Land and automobile enquiries</h2>
            <p class="mt-3 leading-relaxed text-brand-dark/70">Vehicle and property listings may require enquiries, documentation checks and physical inspections before a transaction. Do not treat an enquiry or inspection request as a completed purchase.</p>
            <Link href="/contact" class="mt-4 inline-block font-bold text-brand-orange">Contact BLOMFREE →</Link></section>
    </div></div></AppLayout></template>
