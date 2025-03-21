<template>
    <Head :title="i18n.global.t('head_title')" />

    <CheckoutLayout>
        <div class="lg:flex lg:min-h-full lg:flex-row-reverse lg:overflow-hidden">
            <Cart :cart="cart"></Cart>

            <!-- Checkout form -->
            <section class="flex-auto overflow-y-auto px-4 pb-16 pt-12 sm:px-6 sm:pt-16 lg:px-8 lg:pb-24 lg:pt-8">
                <div class="mx-auto max-w-lg">
                    <div v-if="$page.props.errors.alert" class="pt-8 grid grid-cols-1 gap-y-2">
                        <div
                            v-for="(message, key) in $page.props.errors.alert"
                            :key="key"
                            class="rounded-md bg-red-50 p-4"
                        >
                            <div class="flex">
                                <div class="shrink-0">
                                    <svg class="size-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">{{message}}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="mt-2 grid grid-cols-12 gap-x-4 gap-y-6">
                            <div class="col-span-full">
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
                                        v-if="!logged"
                                        v-model="form.email"
                                        :disabled="form.processing"
                                    />
                                    <input
                                        type="text"
                                        name="email"
                                        id="email"
                                        class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                        placeholder=""
                                        v-if="logged"
                                        :value="customer_email"
                                        :disabled="true"
                                    />

                                    <div class="mt-2" v-show="form.errors.email">
                                        <p class="text-sm text-red-600">
                                            {{ form.errors.email }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-full">
                                <div class="relative">
                                    <label
                                        for="billing_address"
                                        class="z-10 absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                                    >{{ i18n.global.t('address') }}</label>
                                    <input
                                        type="text"
                                        name="billing_address"
                                        id="billing_address"
                                        class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                        :placeholder="'Mińska 25B, lok. U2'"
                                        v-model="form.billing_address"
                                        :disabled="form.processing"
                                    />

                                    <div class="mt-2" v-show="form.errors.billing_address">
                                        <p class="text-sm text-red-600">
                                            {{ form.errors.billing_address }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-full sm:col-span-4">
                                <div class="relative">
                                    <label
                                        for="billing_postal_code"
                                        class="z-10 absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                                    >{{ i18n.global.t('postal_code') }}</label>
                                    <input
                                        type="text"
                                        name="billing_postal_code"
                                        id="billing_postal_code"
                                        class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                        placeholder="03-808"
                                        v-model="form.billing_postal_code"
                                        :disabled="form.processing"
                                    />

                                    <div class="mt-2" v-show="form.errors.billing_postal_code">
                                        <p class="text-sm text-red-600">
                                            {{ form.errors.billing_postal_code }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-full sm:col-span-4">
                                <div class="relative">
                                    <label
                                        for="billing_city"
                                        class="z-10 absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                                    >{{ i18n.global.t('city') }}</label>
                                    <input
                                        type="text"
                                        name="billing_city"
                                        id="billing_city"
                                        class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                        placeholder="Warszawa"
                                        v-model="form.billing_city"
                                        :disabled="form.processing"
                                    />

                                    <div class="mt-2" v-show="form.errors.billing_city">
                                        <p class="text-sm text-red-600">
                                            {{ form.errors.billing_city }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-span-full sm:col-span-4">
                                <div class="relative grid grid-cols-1">
                                    <label
                                        for="billing_country"
                                        class="z-10 absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                                    >{{ i18n.global.t('country') }}</label>
                                    <select
                                        :disabled="form.processing"
                                        v-model="form.billing_country"
                                        id="country"
                                        name="country"
                                        class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pl-3 pr-8 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                    >
                                        <option :value="country" v-for="country in settings.woocommerce_specific_allowed_countries">{{ i18n.global.t(country) }}</option>
                                    </select>
                                    <ChevronDownIcon class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4" aria-hidden="true" />

                                    <div class="mt-2" v-show="form.errors.billing_country">
                                        <p class="text-sm text-red-600">
                                            {{ form.errors.billing_country }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!logged" class="mt-6 flex gap-3">
                            <div class="flex h-5 shrink-0 items-center">
                                <div class="group grid size-4 grid-cols-1">
                                    <input
                                        v-model="form.create_account"
                                        :disabled="form.processing"
                                        id="create_account"
                                        name="create_account"
                                        type="checkbox"
                                        class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto"
                                    />
                                    <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                        <path class="opacity-0 group-has-[:checked]:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        <path class="opacity-0 group-has-[:indeterminate]:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                            <label for="create_account" class="text-sm font-medium text-gray-900">{{ i18n.global.t('create_account') }}</label>
                        </div>

                        <div v-if="!logged && form.create_account" class="mt-6 grid grid-cols-12 gap-x-4 gap-y-8">
                            <div class="col-span-full">
                                <div class="relative">
                                    <label
                                        for="password"
                                        class="z-10 absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                                    >{{ i18n.global.t('password') }}</label>
                                    <input
                                        autocomplete="off"
                                        type="text"
                                        name="password"
                                        id="password"
                                        class="disabled:border-gray-300 disabled:bg-gray-100 disabled:opacity-100 block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                        :placeholder="i18n.global.t('password')"
                                        v-model="form.password"
                                        :disabled="form.processing"
                                    />

                                    <div class="mt-2" v-show="form.errors.password">
                                        <p class="text-sm text-red-600">
                                            {{ form.errors.password }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                            type="submit"
                            class="mt-6 w-full rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >{{ i18n.global.t('continue') }}</button>

                        <p class="mt-6 flex justify-center text-sm font-medium text-gray-500">
                            {{ i18n.global.t('continue_description') }}
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </CheckoutLayout>
</template>

<script setup>

import { inject, watch } from "vue";
import { createI18n } from 'vue-i18n';

import { Head, useForm, usePage } from "@inertiajs/vue3";

import { ChevronDownIcon } from '@heroicons/vue/16/solid'

import CheckoutLayout from "@/Layouts/CheckoutLayout.vue";
import Cart from "@/Pages/Checkout/Cart.vue";

const route = inject('route');

const props = defineProps({
    settings: {
        type: Object,
    },
    cart: {
        type: Object
    },
    create_account: {
        type: Boolean,
        default: false
    },
    password: {
        type: String,
        default: ''
    },
    logged: {
        type: Boolean,
        default: false
    },
    customer_email: {
        type: String
    }
});

const messages = {
    en: {
        head_title: 'Billing information',
        email: 'E-mail address',
        address: 'Street, apartments',
        postal_code: 'Postal code',
        city: 'City',
        country: 'Country',
        create_account: 'Create an account',
        password: 'Password',
        continue: 'Continue',
        continue_description: 'Delivery method will be available in the next step',
        PL: 'Poland'
    },
    pl: {
        head_title: 'Dane odbiorcy',
        email: 'E-mail',
        address: 'Ulica, numer domu, lokal',
        postal_code: 'Kod pocztowy',
        city: 'Miasto',
        country: 'Kraj',
        create_account: 'Utwórz konto',
        password: 'Hasło',
        continue: 'Dalej',
        continue_description: 'Metody dostawy będą dostępne w następnym kroku',
        PL: 'Polska'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const form = useForm({
    email: props.cart.billing_address.email,
    billing_address: props.cart.billing_address.address_1,
    billing_postal_code: props.cart.billing_address.postcode,
    billing_city: props.cart.billing_address.city,
    billing_country: props.cart.billing_address.country,
    create_account: props.create_account,
    password: props.password,
});

watch(
    () => props.cart,
    () => {
        removeItemIds.value = [];
    },
);

const submit = () => {
    form.post(route('cart.update-customer'), {
        errorBag: 'alert',
    });
};

</script>
