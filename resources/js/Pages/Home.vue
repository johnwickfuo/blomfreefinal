<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Sparkles } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import Hero from '@/Components/Hero.vue';
import Section from '@/Components/Section.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Badge from '@/Components/Badge.vue';
import PullQuote from '@/Components/PullQuote.vue';
import CtaBand from '@/Components/CtaBand.vue';
import SubsidiaryCard from '@/Components/SubsidiaryCard.vue';
import { subsidiaries } from '@/data/subsidiaries';

interface FeaturedItem {
    id: number;
    title: string;
    image: string | null;
    meta: string | null;
    href: string;
    price?: number;
    detail?: string | null;
    installments?: boolean;
}

type TabKey = 'lands' | 'animals' | 'collections' | 'gadgets' | 'vehicles';

type FeaturedData = Record<TabKey, FeaturedItem[]>;

const props = withDefaults(
    defineProps<{
        featured?: FeaturedData;
        articles?: { id: number; title: string; excerpt: string; category: string; image: string | null; href: string }[];
        testimonials?: { id: number; name: string; quote: string; division: string | null; image: string | null }[];
        banners?: { id: number; eyebrow: string | null; headline: string; description: string | null; image: string | null; button_label: string | null; button_url: string | null }[];
    }>(),
    {
        articles: () => [],
        testimonials: () => [],
        banners: () => [],
        featured: () => ({
            lands: [],
            animals: [],
            collections: [],
            gadgets: [],
            vehicles: [],
        }),
    },
);

const tabs: { key: TabKey; label: string }[] = [
    { key: 'lands', label: 'Real Estate' },
    { key: 'animals', label: 'Animals' },
    { key: 'collections', label: 'Collections' },
    { key: 'gadgets', label: 'Gadgets' },
    { key: 'vehicles', label: 'Vehicles' },
];

const activeTab = ref<TabKey>('lands');
const activeItems = computed<FeaturedItem[]>(() => props.featured[activeTab.value]);
const activeTabLabel = computed(
    () => tabs.find((t) => t.key === activeTab.value)?.label ?? '',
);

const scrollToSubsidiaries = (): void => {
    document
        .getElementById('subsidiaries')
        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

const page = usePage();
const ceoImage = computed<string>(() => {
    const url = (page.props.branding as { ceo_image_url?: string | null } | undefined)
        ?.ceo_image_url;
    return url || '/images/ceo.jpg';
});
</script>

<template>
    <SeoMeta
        title="BLOMFREE & CO. — Quality is Priceless"
        description="Land, premium livestock, fashion and gadgets — one trusted Nigerian group across four industries."
    />

    <AppLayout>
        <!-- 1. Hero -->
        <Hero :overlay="false" min-height-class="min-h-[90vh]">
            <template #overlay>
                <!-- Subtle orange radial gradient -->
                <div
                    class="absolute inset-0"
                    style="
                        background: radial-gradient(
                            circle at 72% 32%,
                            rgba(245, 130, 32, 0.22),
                            transparent 58%
                        );
                    "
                    aria-hidden="true"
                />
                <!-- CEO portrait (desktop only) -->
                <div
                    class="absolute inset-y-0 right-0 hidden w-[46%] lg:block xl:w-[42%]"
                >
                    <img
                        :src="ceoImage"
                        alt="BLOMFREE company leadership"
                        class="h-full w-full object-cover object-top"
                        fetchpriority="high"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/60 to-transparent"
                        aria-hidden="true"
                    />
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-brand-dark/70 via-transparent to-transparent"
                        aria-hidden="true"
                    />
                </div>
            </template>

            <template #eyebrow>
                <Badge variant="info">BLOMFREE &amp; CO. NIG. LTD</Badge>
            </template>
            <template #title>
                <h1
                    class="text-4xl font-extrabold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl"
                >
                    One Company.
                    <span class="text-brand-orange">
                        Endless Possibilities.
                    </span>
                </h1>
            </template>
            <template #subtitle>
                From real estate and automobiles to fashion, technology and livestock, BLOMFREE brings quality and opportunity together.
            </template>
            <template #actions>
                <Button size="lg" @click="scrollToSubsidiaries">
                    Explore Our Businesses
                </Button>
                <Button href="/contact" variant="outline" size="lg">
                    Contact Us
                </Button>
            </template>
        </Hero>

        <!-- 2. Orange callout band -->
        <Link
            href="/about"
            class="group block bg-brand-orange transition-colors duration-300 hover:bg-brand-orangeDark"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-7 sm:px-6 sm:py-8 lg:px-8"
            >
                <p
                    class="text-xl font-extrabold tracking-tight text-white sm:text-2xl"
                >
                    Why Haven&rsquo;t You Patronized Us?
                </p>
                <span
                    class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-white"
                >
                    <span class="hidden sm:inline">Find out why</span>
                    <ArrowRight
                        class="h-5 w-5 transition-transform duration-300 group-hover:translate-x-1"
                    />
                </span>
            </div>
        </Link>

        <!-- 3. CEO intro -->
        <Section>
            <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                <div v-reveal class="relative">
                    <div
                        class="absolute -bottom-5 -right-5 h-2/3 w-2/3 rounded-3xl bg-brand-orange/15"
                        aria-hidden="true"
                    />
                    <img
                        :src="ceoImage"
                        alt="BLOMFREE company leadership"
                        loading="lazy"
                        class="relative aspect-[4/5] w-full rounded-3xl object-cover object-top shadow-xl"
                    />
                </div>

                <div v-reveal="120" class="flex flex-col gap-6">
                    <div>
                        <Badge variant="info">Leadership</Badge>
                        <h2
                            class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                        >
                            Meet Saturday Emomotimi Charles, CEO
                        </h2>
                    </div>
                    <p class="text-base leading-relaxed text-brand-dark/70">BLOMFREE &amp; CO. NIG. LTD brings several businesses together around one guiding idea: Quality is Priceless. Explore our story and the people behind the company.</p>
                    <Button href="/about" variant="outline">Read our story</Button>
                </div>
            </div>
        </Section>

        <!-- 4. Subsidiaries grid -->
        <Section id="subsidiaries">
            <div v-reveal class="max-w-2xl">
                <Badge variant="info">Our Divisions</Badge>
                <h2
                    class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                >
                    Five businesses, one commitment to quality
                </h2>
                <p class="mt-4 text-base text-brand-dark/70">
                    Discover our real estate, animals, fashion, technology, and automobile divisions.
                </p>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                <SubsidiaryCard
                    v-for="(item, i) in subsidiaries"
                    :key="item.name"
                    v-reveal="i * 80"
                    :name="item.name"
                    :tagline="item.tagline"
                    :href="item.href"
                    :icon="item.icon"
                    :image="item.image"
                    :coming-soon="item.comingSoon"
                />
            </div>
        </Section>

        <!-- 5. Featured listings -->
        <Section>
            <div
                v-reveal
                class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between"
            >
                <div class="max-w-xl">
                    <Badge variant="info">Handpicked</Badge>
                    <h2
                        class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl"
                    >
                        Featured listings
                    </h2>
                    <p class="mt-4 text-base text-brand-dark/70">
                        A preview of standout offerings from across our
                        subsidiaries.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        type="button"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition-colors"
                        :class="
                            activeTab === tab.key
                                ? 'bg-brand-dark text-white'
                                : 'bg-white text-brand-dark/70 ring-1 ring-black/5 hover:text-brand-dark'
                        "
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <div v-reveal="100" class="mt-10">
                <div
                    v-if="activeItems.length"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <Card
                        v-for="item in activeItems"
                        :key="item.id"
                        class="overflow-hidden"
                    >
                        <div class="aspect-[4/3] w-full bg-brand-cream">
                            <img
                                v-if="item.image"
                                loading="lazy"
                                :src="item.image"
                                :alt="item.title"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="flex flex-col gap-2 p-5">
                            <h3 class="font-bold tracking-tight">
                                {{ item.title }}
                            </h3>
                            <p v-if="item.price" class="text-lg font-extrabold text-brand-dark">₦{{ item.price.toLocaleString() }}</p>
                            <p v-if="item.detail" class="text-xs text-brand-dark/60">{{ item.detail }}</p>
                            <p v-if="item.installments" class="text-xs font-semibold text-brand-orange">Installments available</p>
                            <p
                                v-if="item.meta"
                                class="text-sm text-brand-dark/60"
                            >
                                {{ item.meta }}
                            </p>
                            <Link
                                :href="item.href"
                                class="mt-1 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-orange transition-colors hover:text-brand-orangeDark"
                            >
                                View details
                                <ArrowRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </Card>
                </div>

                <!-- Coming Soon empty state -->
                <div
                    v-else
                    class="flex flex-col items-center justify-center gap-3 rounded-3xl border border-dashed border-brand-dark/15 bg-white/60 px-6 py-16 text-center"
                >
                    <Sparkles class="h-9 w-9 text-brand-orange/60" />
                    <p class="text-lg font-bold tracking-tight">Coming Soon</p>
                    <p class="max-w-sm text-sm text-brand-dark/55">
                        Featured {{ activeTabLabel }} will appear here as our
                        {{ activeTabLabel.toLowerCase() }} catalogue goes live.
                    </p>
                </div>
            </div>
        </Section>

        <Section>
            <div class="grid overflow-hidden rounded-3xl bg-brand-dark text-white lg:grid-cols-2">
                <div class="p-8 sm:p-12 lg:p-16">
                    <Badge variant="info">Now available</Badge>
                    <h2 class="mt-4 text-3xl font-extrabold sm:text-4xl">Meet BLOMWHEELS Automobile</h2>
                    <p class="mt-5 max-w-lg leading-relaxed text-white/75">Browse our latest brand-new, foreign-used and Nigerian-used vehicle listings, compare options, and book an inspection for available vehicles.</p>
                    <Button href="/automobiles" size="lg" class="mt-8">Explore BLOMWHEELS vehicles</Button>
                </div>
                <div class="min-h-64 bg-gradient-to-br from-brand-orange via-orange-600 to-brand-dark p-10 lg:min-h-full">
                    <div class="flex h-full items-end rounded-2xl border border-white/25 p-8">
                        <span class="text-4xl font-black tracking-tight sm:text-5xl">BLOMWHEELS<span class="block text-lg font-medium">Find your next drive.</span></span>
                    </div>
                </div>
            </div>
        </Section>
        <Section>
            <div class="grid gap-10 lg:grid-cols-2">
                <div><Badge variant="info">Why BLOMFREE</Badge><h2 class="mt-3 text-3xl font-extrabold">Quality across every division</h2><p class="mt-4 leading-relaxed text-brand-dark/70">Browse clear listings, compare available options, and contact our team when you need help. Eligible purchases support secure online payment.</p></div>
                <div class="grid gap-4 sm:grid-cols-2"><div v-for="reason in ['Real listings and clear details', 'Five connected businesses', 'Support when you need it', 'Secure payment for eligible orders']" :key="reason" class="rounded-2xl bg-white p-6 font-semibold shadow-sm ring-1 ring-black/5">{{ reason }}</div></div>
            </div>
        </Section>
        <Section>
            <div class="rounded-3xl bg-white p-7 shadow-sm sm:p-12">
                <Badge variant="info">Flexible payments</Badge>
                <h2 class="mt-3 text-3xl font-extrabold">Plan eligible purchases</h2>
                <p class="mt-4 max-w-2xl text-brand-dark/70">Installment plans are available for selected land and gadgets where enabled. Review the down payment and terms before applying, then track progress in your account.</p>
                <ol class="mt-7 grid gap-4 sm:grid-cols-4"><li v-for="(step, i) in ['Choose an eligible item', 'Review terms', 'Apply and pay', 'Track your plan']" :key="step" class="rounded-xl bg-brand-cream p-5"><span class="font-bold text-brand-orange">0{{ i + 1 }}</span><p class="mt-2 font-semibold">{{ step }}</p></li></ol>
                <Link href="/installments" class="mt-7 inline-block font-semibold text-brand-orange hover:underline">Learn about installments →</Link>
            </div>
        </Section>
        <Section>
            <h2 class="text-3xl font-extrabold">How BLOMFREE works</h2>
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4"><div v-for="(step, i) in [{title:'Explore',text:'Browse real products, animals and land.'},{title:'Choose',text:'Review images, prices and available options.'},{title:'Connect or purchase',text:'Order eligible items online or request an inspection.'},{title:'Track',text:'Follow orders or applications through your account.'}]" :key="step.title" class="rounded-2xl border border-brand-dark/10 p-6"><span class="text-sm font-bold text-brand-orange">0{{ i + 1 }}</span><h3 class="mt-3 text-lg font-bold">{{ step.title }}</h3><p class="mt-2 text-sm text-brand-dark/65">{{ step.text }}</p></div></div>
        </Section>

        <Section v-if="banners.length">
            <div class="grid gap-5 lg:grid-cols-2">
                <article v-for="banner in banners" :key="banner.id" class="overflow-hidden rounded-3xl bg-brand-dark text-white">
                    <img v-if="banner.image" :src="banner.image" :alt="banner.headline" loading="lazy" class="aspect-[16/7] w-full object-cover" />
                    <div class="p-7 sm:p-10"><p v-if="banner.eyebrow" class="text-sm font-bold uppercase tracking-wider text-brand-orange">{{ banner.eyebrow }}</p><h2 class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ banner.headline }}</h2><p v-if="banner.description" class="mt-3 text-white/75">{{ banner.description }}</p><Link v-if="banner.button_label && banner.button_url" :href="banner.button_url" class="mt-5 inline-block rounded-full bg-brand-orange px-5 py-3 font-bold text-white">{{ banner.button_label }}</Link></div>
                </article>
            </div>
        </Section>
        <Section v-if="testimonials.length">
            <Badge variant="info">Customer voices</Badge><h2 class="mt-3 text-3xl font-extrabold">What customers say</h2>
            <div class="mt-8 grid gap-5 md:grid-cols-3"><blockquote v-for="item in testimonials" :key="item.id" class="rounded-2xl bg-white p-6 shadow-sm"><p class="leading-relaxed">“{{ item.quote }}”</p><footer class="mt-5 flex items-center gap-3"><img v-if="item.image" :src="item.image" :alt="item.name" loading="lazy" class="h-10 w-10 rounded-full object-cover" /><div><strong class="block text-sm">{{ item.name }}</strong><span v-if="item.division" class="text-xs text-brand-dark/60">{{ item.division }}</span></div></footer></blockquote></div>
        </Section>
        <Section v-if="articles.length">
            <div class="flex flex-wrap items-end justify-between gap-4"><div><Badge variant="info">Insights</Badge><h2 class="mt-3 text-3xl font-extrabold">Ideas and guides</h2></div><Link href="/insights" class="font-semibold text-brand-orange hover:underline">All articles →</Link></div>
            <div class="mt-8 grid gap-5 md:grid-cols-3"><article v-for="article in articles" :key="article.id" class="overflow-hidden rounded-2xl bg-white shadow-sm"><img v-if="article.image" :src="article.image" :alt="article.title" loading="lazy" class="aspect-[16/10] w-full object-cover" /><div class="p-6"><p class="text-xs font-bold uppercase text-brand-orange">{{ article.category }}</p><h3 class="mt-2 text-xl font-bold">{{ article.title }}</h3><p class="mt-3 line-clamp-3 text-sm text-brand-dark/70">{{ article.excerpt }}</p><Link :href="article.href" class="mt-5 inline-block font-semibold text-brand-orange">Read article →</Link></div></article></div>
        </Section>

        <!-- 7. Final CTA band -->
        <CtaBand
            title="Whatever You’re Looking For, Quality Comes First."
            subtitle="Reach out today — our team is ready to help you find land, livestock, fashion, or the latest gadgets."
        />
    </AppLayout>
</template>
