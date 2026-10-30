<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { Heart, GitCompareArrows, MapPin, CalendarDays } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import JsonLd from '@/Components/JsonLd.vue';
import Lightbox from '@/Components/Lightbox.vue';
import VehicleCard from '@/Components/VehicleCard.vue';
import type { VehicleCardData, VehicleDetails } from '@/types/vehicles';
import { conditionLabel, formatVehiclePrice } from '@/types/vehicles';
import { readVehicleIds, toggleVehicleId, vehicleCollectionUrl } from '@/lib/vehicleCollections';

const props = defineProps<{ vehicle: VehicleDetails; related: VehicleCardData[] }>();
const lightbox = ref<number | null>(null);
const saved = ref(false);
const compared = ref(false);
const inquiryVisible = ref(false);
const form = useForm({
    name: '', email: '', phone: '', subject: 'automobiles',
    vehicle_id: props.vehicle.id, inquiry_category: 'general', message: '',
    related_url: props.vehicle.href, website: ''
});
function sync(): void {
    saved.value = readVehicleIds('blomwheels-saved').includes(props.vehicle.id);
    compared.value = readVehicleIds('blomwheels-compare').includes(props.vehicle.id);
}
function toggle(key: 'blomwheels-saved' | 'blomwheels-compare'): void {
    if (key === 'blomwheels-compare' && !compared.value && readVehicleIds(key).length >= 3) {
        window.alert('Compare up to three vehicles.'); return;
    }
    toggleVehicleId(key, props.vehicle.id, key === 'blomwheels-compare' ? 3 : 30);
    sync();
}
onMounted(sync);
const carSchema = computed(() => ({
    '@context': 'https://schema.org', '@type': 'Car',
    name: props.vehicle.title, description: props.vehicle.description,
    image: props.vehicle.gallery,
    brand: { '@type': 'Brand', name: props.vehicle.make },
    model: props.vehicle.model, vehicleModelDate: String(props.vehicle.year),
    itemCondition: props.vehicle.condition === 'brand_new' ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
    mileageFromOdometer: props.vehicle.mileage_km === null ? undefined : { '@type': 'QuantitativeValue', value: props.vehicle.mileage_km, unitCode: 'KMT' },
    offers: { '@type': 'Offer', price: props.vehicle.price, priceCurrency: 'NGN', availability:
        props.vehicle.status === 'available' ? 'https://schema.org/InStock' :
        props.vehicle.status === 'on_order' ? 'https://schema.org/PreOrder' : 'https://schema.org/OutOfStock' }
}));
const whatsapp = computed(() => 'https://wa.me/2348103965317?text=' + encodeURIComponent('Hello BLOMWHEELS, I would like to ask about ' + props.vehicle.title + ' (' + props.vehicle.stock_reference + ').'));
const specifications = computed(() => [
    ['Make', props.vehicle.make], ['Model', props.vehicle.model], ['Year', String(props.vehicle.year)],
    ['Condition', conditionLabel(props.vehicle.condition)], ['Mileage', props.vehicle.mileage_km !== null ? props.vehicle.mileage_km.toLocaleString() + ' km' : null],
    ['Body', props.vehicle.body_type], ['Transmission', props.vehicle.transmission],
    ['Fuel', props.vehicle.fuel_type], ['Engine', props.vehicle.engine], ['Drivetrain', props.vehicle.drivetrain],
    ['Seats', props.vehicle.seats], ['Exterior', props.vehicle.exterior_color],
    ['Interior', props.vehicle.interior_color],
].filter((item) => item[1] !== null && item[1] !== ''));
function submitInquiry(): void { form.post('/inquiries', { preserveScroll: true, onSuccess: () => form.reset('message') }); }
</script>

<template>
    <SeoMeta :title="vehicle.title + ' — BLOMWHEELS Automobile'" :description="vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model + ' for sale through BLOMWHEELS. View vehicle details and book an inspection.'" :image="vehicle.image ?? undefined" />
    <JsonLd :schema="carSchema" />
    <AppLayout>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <Link href="/automobiles" class="text-sm font-semibold text-brand-orange">← Back to all vehicles</Link>
            <div class="mt-7 grid gap-10 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]">
                <div>
                    <button v-if="vehicle.gallery.length" type="button" aria-label="Open vehicle gallery" class="aspect-[16/10] w-full overflow-hidden rounded-3xl bg-gray-100" @click="lightbox = 0"><img :src="vehicle.gallery[0]" :alt="vehicle.title" class="h-full w-full object-cover" /></button>
                    <div v-else class="flex aspect-[16/10] items-center justify-center rounded-3xl bg-gray-100 text-brand-dark/50">Photos not yet available</div>
                    <div v-if="vehicle.gallery.length > 1" class="mt-3 grid grid-cols-4 gap-3 sm:grid-cols-6">
                        <button v-for="(image, i) in vehicle.gallery.slice(1)" :key="i" type="button" class="aspect-square overflow-hidden rounded-xl bg-gray-100" @click="lightbox = i + 1"><img :src="image" alt="" class="h-full w-full object-cover" /></button>
                    </div>
                    <div class="mt-9"><h2 class="text-2xl font-extrabold">Vehicle overview</h2><p class="mt-4 whitespace-pre-line leading-relaxed text-brand-dark/75">{{ vehicle.description }}</p></div>
                    <a v-if="vehicle.video_url" :href="vehicle.video_url" target="_blank" rel="noopener noreferrer" class="mt-6 inline-block font-semibold text-brand-orange underline">Watch vehicle video</a>
                    <div class="mt-10"><h2 class="text-2xl font-extrabold">Specifications</h2><dl class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div v-for="[label, value] in specifications" :key="String(label)" class="rounded-xl bg-white p-4 ring-1 ring-black/5"><dt class="text-xs text-brand-dark/60">{{ label }}</dt><dd class="mt-1 font-bold">{{ value }}</dd></div>
                    </dl></div>
                    <div v-if="vehicle.features.length" class="mt-10"><h2 class="text-2xl font-bold">Features</h2><ul class="mt-4 grid gap-2 sm:grid-cols-2"><li v-for="feature in vehicle.features" :key="feature" class="rounded-xl bg-white px-4 py-3">✓ {{ feature }}</li></ul></div>
                    <div class="mt-10 rounded-2xl bg-white p-6 ring-1 ring-black/5">
                        <h2 class="text-xl font-bold">Condition & documentation</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div v-for="[label, value] in [['Accident history',vehicle.accident_history],['Service history',vehicle.service_history],['Customs status',vehicle.customs_status]]" :key="String(label)"><dt class="font-semibold">{{ label }}</dt><dd class="text-brand-dark/70">{{ value || 'Not verified / not provided' }}</dd></div>
                        </dl>
                        <div v-if="vehicle.documents.length" class="mt-4"><h3 class="font-semibold">Documents recorded</h3><p class="mt-1 text-sm text-brand-dark/70">{{ vehicle.documents.join(', ') }}</p></div>
                        <div v-if="vehicle.known_issues.length" class="mt-4"><h3 class="font-semibold">Disclosed issues</h3><ul class="mt-2 list-inside list-disc text-sm"><li v-for="issue in vehicle.known_issues" :key="issue">{{ issue }}</li></ul></div>
                        <p class="mt-4 text-xs text-brand-dark/50">Confirm vehicle condition and documentation independently before making a purchase decision.</p>
                    </div>
                </div>
                <aside class="lg:sticky lg:top-28 lg:self-start">
                    <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-black/5">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold"><span class="rounded-full bg-orange-50 px-3 py-1 text-brand-orange">{{ conditionLabel(vehicle.condition) }}</span><span class="rounded-full bg-gray-100 px-3 py-1">{{ vehicle.status }}</span></div>
                        <h1 class="mt-4 text-3xl font-black">{{ vehicle.title }}</h1>
                        <p class="mt-2 flex items-center gap-2 text-sm text-brand-dark/65"><MapPin class="h-4 w-4" /> {{ [vehicle.city,vehicle.state].filter(Boolean).join(', ') || 'Location on request' }}</p>
                        <p class="mt-5 text-3xl font-extrabold text-brand-orange">{{ formatVehiclePrice(vehicle.price) }}</p>
                        <p v-if="vehicle.negotiable" class="text-xs font-semibold text-brand-dark/50">Price is negotiable</p>
                        <p class="mt-2 text-xs text-brand-dark/45">Stock reference: {{ vehicle.stock_reference }}</p>
                        <div v-if="vehicle.status === 'available'" class="mt-6 space-y-3">
                            <Link :href="vehicle.href + '/inspect'" class="flex items-center justify-center gap-2 rounded-xl bg-brand-orange px-4 py-3 font-bold text-white"><CalendarDays class="h-4 w-4" /> Request inspection</Link>
                            <button class="w-full rounded-xl border border-brand-dark px-4 py-3 font-bold" @click="inquiryVisible = !inquiryVisible">Make an inquiry</button>
                            <a :href="whatsapp" target="_blank" rel="noopener noreferrer" class="block rounded-xl border border-gray-200 px-4 py-3 text-center font-semibold">Ask on WhatsApp</a>
                        </div>
                        <p v-else class="mt-5 rounded-xl bg-orange-50 p-4 text-sm">This vehicle is not currently available for inspection or inquiry.</p>
                        <div class="mt-4 flex gap-3">
                            <button class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gray-100 p-3 text-sm" @click="toggle('blomwheels-saved')"><Heart class="h-4 w-4" /> {{ saved ? 'Saved' : 'Save' }}</button>
                            <button class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-gray-100 p-3 text-sm" @click="toggle('blomwheels-compare')"><GitCompareArrows class="h-4 w-4" /> {{ compared ? 'Added' : 'Compare' }}</button>
                        </div>
                        <a :href="vehicleCollectionUrl('compare')" class="mt-2 block text-center text-xs font-semibold text-brand-orange">View comparison</a>
                    </div>
                    <form v-if="inquiryVisible && vehicle.status === 'available'" class="mt-4 space-y-4 rounded-2xl bg-white p-6 ring-1 ring-black/5" @submit.prevent="submitInquiry">
                        <h2 class="text-lg font-bold">Ask about this vehicle</h2>
                        <label class="block text-sm">Name<input v-model="form.name" required class="mt-1 w-full rounded-xl border-gray-300" /><span class="text-red-600">{{ form.errors.name }}</span></label>
                        <label class="block text-sm">Email<input v-model="form.email" type="email" required class="mt-1 w-full rounded-xl border-gray-300" /><span class="text-red-600">{{ form.errors.email }}</span></label>
                        <label class="block text-sm">Phone<input v-model="form.phone" type="tel" required class="mt-1 w-full rounded-xl border-gray-300" /><span class="text-red-600">{{ form.errors.phone }}</span></label>
                        <label class="block text-sm">Inquiry type<select v-model="form.inquiry_category" class="mt-1 w-full rounded-xl border-gray-300"><option value="general">General</option><option value="negotiation">Price negotiation</option><option value="availability">Availability</option><option value="documents">Documentation</option><option value="inspection">Inspection</option><option value="other">Other</option></select></label>
                        <label class="block text-sm">Message<textarea v-model="form.message" required minlength="10" rows="4" class="mt-1 w-full rounded-xl border-gray-300" /><span class="text-red-600">{{ form.errors.message }}</span></label>
                        <input v-model="form.website" type="text" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
                        <button type="submit" :disabled="form.processing" class="w-full rounded-xl bg-brand-dark px-4 py-3 font-semibold text-white disabled:opacity-50">{{ form.processing ? 'Sending…' : 'Send inquiry' }}</button>
                    </form>
                </aside>
            </div>
            <section v-if="related.length" class="mt-16"><h2 class="mb-6 text-2xl font-bold">Similar vehicles</h2><div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"><VehicleCard v-for="item in related" :key="item.id" :vehicle="item" /></div></section>
        </div>
        <Lightbox v-model="lightbox" :images="vehicle.gallery" />
    </AppLayout>
</template>
