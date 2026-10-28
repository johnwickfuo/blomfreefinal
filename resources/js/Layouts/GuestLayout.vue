<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <main class="min-h-screen bg-[#FFF8F2] text-[#1A1A1A] lg:grid lg:grid-cols-2">
        <aside class="hidden flex-col justify-between bg-[#1A1A1A] p-12 text-white lg:flex">
            <Link href="/" class="text-2xl font-extrabold tracking-wide text-[#F58220]">BLOMFREE</Link>
            <div>
                <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-[#F58220]">BLOMFREE & CO. NIG. LTD</p>
                <h1 class="max-w-lg text-5xl font-bold leading-tight">Welcome to a better way to shop.</h1>
                <p class="mt-6 max-w-md text-lg text-gray-300">Explore our collections, manage your orders, and stay connected to everything BLOMFREE.</p>
            </div>
            <p class="text-sm text-gray-400">Your account, all in one place.</p>
        </aside>
        <section class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <Link href="/" class="mb-8 inline-block text-2xl font-extrabold tracking-wide text-[#F58220] lg:hidden">BLOMFREE</Link>
                <div class="rounded-2xl bg-white p-6 shadow-xl shadow-orange-100/60 sm:p-9">
                    <slot />
                </div>
                <Link href="/" class="mt-6 block text-center text-sm font-medium text-gray-600 underline decoration-[#F58220] underline-offset-4 hover:text-[#F58220]">Back to website</Link>
            </div>
        </section>
    </main>
</template>
