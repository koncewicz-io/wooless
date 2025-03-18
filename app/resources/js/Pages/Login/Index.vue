<template>
    <Head :title="i18n.global.t('head_title')" />

    <AppLayout :cart="cart" :logged="logged">
        <div class="flex min-h-full flex-1 flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">{{ i18n.global.t('login_to_account') }}</h2>
            </div>

            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <div class="mt-10" v-if="$page.props.errors.exception">
                    <div class="grid grid-cols-1 gap-y-2 mx-auto">
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg v-if="false" class="size-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                                    </svg>
                                    <XCircleIcon class="size-5 text-red-400" aria-hidden="true" />
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">{{$page.props.errors.exception}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit" class="mt-10 space-y-6">
                    <div class="relative">
                        <label
                            for="email"
                            class="z-10 absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                        >{{ i18n.global.t('email') }}</label>
                        <input
                            type="text"
                            name="email"
                            id="email"
                            class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                            placeholder="marek@koncewicz.io"
                            autocomplete="email"
                            v-model="form.email"
                            :disabled="form.processing"
                        />
                    </div>

                    <div class="relative">
                        <label
                            for="password"
                            class="z-10 absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                        >{{ i18n.global.t('password') }}</label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                            placeholder="••••••••"
                            autocomplete="email"
                            v-model="form.password"
                            :disabled="form.processing"
                        />
                    </div>

                    <div>
                        <button
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                            type="submit"
                            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >{{ i18n.global.t('login') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>

import { createI18n } from 'vue-i18n';

import { Head, useForm, usePage } from "@inertiajs/vue3";
import { XCircleIcon } from "@heroicons/vue/20/solid";

import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    cart: {
        type: Object,
    },
    logged: {
        type: Boolean,
        default: false
    }
});

const messages = {
    en: {
        head_title: 'Log in',
        email: 'E-mail address',
        password: 'Password',
        login: 'Log in',
        login_to_account: 'Log in to your account'
    },
    pl: {
        head_title: 'Logowanie',
        email: 'E-mail',
        password: 'Hasło',
        login: 'Zaloguj się',
        login_to_account: 'Zaloguj się'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const form = useForm({
    email: null,
    password: null
});

const submit = () => {
    form.post(route('login.store'));
};

</script>
