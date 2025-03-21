<template>
    <div class="bg-white h-full">
        <header class="relative border-b border-gray-200 bg-white text-sm font-medium text-gray-700">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="h-20 relative flex items-center justify-end sm:justify-center">
                    <Link :href="route('home.index')" class="absolute left-0 top-1/2 -mt-4">
                        <span class="sr-only">Your Company</span>
                        <svg class="h-8 w-auto" viewBox="0 0 47 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#4f46e5" d="M23.5 6.5C17.5 6.5 13.75 9.5 12.25 15.5C14.5 12.5 17.125 11.375 20.125 12.125C21.8367 12.5529 23.0601 13.7947 24.4142 15.1692C26.6202 17.4084 29.1734 20 34.75 20C40.75 20 44.5 17 46 11C43.75 14 41.125 15.125 38.125 14.375C36.4133 13.9471 35.1899 12.7053 33.8357 11.3308C31.6297 9.09158 29.0766 6.5 23.5 6.5ZM12.25 20C6.25 20 2.5 23 1 29C3.25 26 5.875 24.875 8.875 25.625C10.5867 26.0529 11.8101 27.2947 13.1642 28.6693C15.3702 30.9084 17.9234 33.5 23.5 33.5C29.5 33.5 33.25 30.5 34.75 24.5C32.5 27.5 29.875 28.625 26.875 27.875C25.1633 27.4471 23.9399 26.2053 22.5858 24.8307C20.3798 22.5916 17.8266 20 12.25 20Z"/>
                        </svg>
                    </Link>
                    <nav aria-label="Progress" class="hidden sm:block">
                        <ol role="list" class="flex space-x-4">
                            <li v-for="(step, stepIdx) in steps" :key="step.name" class="flex items-center">
                                <Link v-if="step.status === 'current'" :href="step.href" aria-current="page" class="text-indigo-600">{{ step.name }}</Link>
                                <Link v-else :href="step.href">{{ step.name }}</Link>
                                <ChevronRightIcon v-if="stepIdx !== steps.length - 1" class="ml-4 size-5 text-gray-300" aria-hidden="true" />
                            </li>
                        </ol>
                    </nav>
                    <p class="sm:hidden">{{ i18n.global.t('step') }} {{ step() }} of 4</p>
                </div>
            </div>
        </header>

        <main
            class="relative mx-auto max-w-7xl"
            :class="['h-[calc(100%-5rem-1px)]']"
        >
            <slot />
        </main>
    </div>
</template>

<script setup>
import { inject, computed } from "vue";
import { createI18n } from 'vue-i18n';

import { Link, usePage } from "@inertiajs/vue3";
import { ChevronRightIcon } from "@heroicons/vue/20/solid";

const route = inject('route');

const messages = {
    en: {
        cart: 'Cart',
        billing_information: 'Billing information',
        shipping: 'Shipping',
        payment: 'Payment',
        step: 'Step'
    },
    pl: {
        cart: 'Koszyk',
        billing_information: 'Dane odbiorcy',
        shipping: 'Dostawa',
        payment: 'Płatność',
        step: 'Krok'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const steps = [
    {name: i18n.global.t('cart'), href: route('cart.index'), status: 'complete'},
    {name: i18n.global.t('billing_information'), href: route('checkout.index'), status: route().current('checkout.index') ? 'current' : 'complete'},
    {name: i18n.global.t('shipping'), href: route('checkout.shipping'), status: route().current('checkout.shipping') ? 'current' : 'upcoming'},
    {name: i18n.global.t('payment'), href: route('checkout.payment'), status: route().current('checkout.payment') ? 'current' : 'upcoming'},
]

const step = computed(() => () => {
    if (route().current('checkout.index')) {
        return 2;
    }

    if (route().current('checkout.shipping')) {
        return 3;
    }

    if (route().current('checkout.payment')) {
        return 4;
    }

    return 0;
})

</script>
