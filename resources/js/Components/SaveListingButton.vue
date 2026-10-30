<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Heart } from 'lucide-vue-next';
import { getSavedIds, toggleSavedListing } from '@/lib/savedListings';
import type { SavedType } from '@/lib/savedListings';
const props = defineProps<{ type: SavedType; id: number }>();
const isSaved = ref(false);
function update(): void { isSaved.value = getSavedIds(props.type).includes(props.id); }
function toggle(): void { isSaved.value = toggleSavedListing(props.type, props.id); }
onMounted(() => {
    update();
    window.addEventListener('blomfree-saved-changed', update);
    window.addEventListener('blomwheels-list-changed', update);
});
onBeforeUnmount(() => {
    window.removeEventListener('blomfree-saved-changed', update);
    window.removeEventListener('blomwheels-list-changed', update);
});
</script>
<template>
    <button type="button" :aria-pressed="isSaved"
        :aria-label="isSaved ? 'Remove from saved listings' : 'Save this listing'"
        class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold hover:border-brand-orange"
        @click="toggle">
        <Heart class="h-4 w-4" :class="isSaved ? 'fill-brand-orange text-brand-orange' : 'text-brand-dark/60'" />
        {{ isSaved ? 'Saved' : 'Save listing' }}
    </button>
</template>
