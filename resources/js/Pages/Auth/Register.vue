<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref } from 'vue';
const showPassword = ref(false);
import Button from '@/Components/Button.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = (): void => {
    form.post('/register', { onSuccess: () => form.reset('password', 'password_confirmation') });
};
</script>

<template>
    <Head title="Create account" />
    <GuestLayout>
            <h1 class="text-3xl font-extrabold tracking-tight">Create your account</h1>
            <p class="mt-2 text-brand-dark/70">
                Already have one? <Link href="/login" class="font-semibold text-brand-orange hover:underline">Log in</Link>.
            </p>

            <form @submit.prevent="submit" class="mt-8 space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold">Full name</label>
                    <input id="name" v-model="form.name" type="text" required class="input mt-1" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold">Email</label>
                    <input id="email" v-model="form.email" type="email" required class="input mt-1" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-semibold">Phone <span class="text-xs text-brand-dark/50">(optional)</span></label>
                    <input id="phone" v-model="form.phone" type="tel" placeholder="0810 123 4567" class="input mt-1" />
                    <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                </div>
                <div>
                    <label for="password" class="block text-sm font-semibold">Password</label>
                    <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required class="input mt-1" />
                    <button type="button" class="mt-2 text-sm font-medium text-brand-orange" @click="showPassword = !showPassword">{{ showPassword ? 'Hide password' : 'Show password' }}</button>
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold">Confirm password</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" required class="input mt-1" />
                    <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-600">{{ form.errors.password_confirmation }}</p>
                </div>

                <Button type="submit" :disabled="form.processing" size="lg">
                    {{ form.processing ? 'Creating account…' : 'Create account' }}
                </Button>
            </form>
    </GuestLayout>
</template>

<style scoped>
.input {
    @apply w-full rounded-lg border border-black/15 bg-white px-3 py-2 text-sm focus:border-brand-orange focus:outline-none focus:ring-2 focus:ring-brand-orange/30;
}
</style>
