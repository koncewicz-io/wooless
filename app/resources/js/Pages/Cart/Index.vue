<template>
    <Head :title="i18n.global.t('head_title')" />

    <AppLayout :cart="props.cart" :logged="logged">
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

        <div
            class=""
            :class="[cart.items.length ? 'pt-16' : 'pt-24 pb-16']"
        >
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ i18n.global.t('cart') }}</h1>
            <p
                v-if="!cart.items.length"
                class="mt-4 text-base text-gray-500"
            >
                {{ i18n.global.t('cart_empty') }}
            </p>

            <form v-if="cart.items.length" @submit.prevent="submit" class="mt-12 lg:grid lg:grid-cols-12 lg:items-start lg:gap-x-12 xl:gap-x-16">
                <section aria-labelledby="cart-heading" class="lg:col-span-7">
                    <h2 id="cart-heading" class="sr-only">Items in your shopping cart</h2>

                    <ul role="list" class="divide-y divide-gray-200 border-t border-gray-200">
                        <li v-for="product in cart.items" :key="product.id" class="flex py-6 sm:py-10">
                            <div class="shrink-0">
                                <img
                                    :src="product.images[0]?.src"
                                    :srcset="product.images[0]?.srcset"
                                    :sizes="product.images[0]?.sizes || '(max-width: 640px) 100vw, (max-width: 768px) 50vw, 33vw'"
                                    :alt="product.images[0]?.alt"
                                    class="size-24 rounded-md object-cover sm:size-48"
                                />
                            </div>

                            <div class="ml-4 flex flex-1 flex-col justify-between sm:ml-6">
                                <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                    <div>
                                        <div class="flex justify-between">
                                            <h3 class="text-sm">
                                                <Link :href="route('product.show', product.id)" class="font-medium text-gray-700 hover:text-gray-800">{{ product.name }}</Link>
                                            </h3>
                                        </div>
                                        <div class="mt-1 flex text-sm">
                                            <p class="text-gray-500">{{ product.color }}</p>
                                            <p v-if="product.size" class="ml-4 border-l border-gray-200 pl-4 text-gray-500">{{ product.size }}</p>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-gray-900"><span v-if="product.prices.currency_prefix" v-html="product.prices.currency_prefix"></span>{{ product.formatted_prices.sale_price }}<span v-if="product.prices.currency_suffix" v-html="product.prices.currency_suffix"></span></p>
                                    </div>

                                    <div class="mt-4 sm:mt-0 sm:pr-9">
                                        <div class="flex items-center -ml-2">
                                            <form @submit.prevent="updateItem(product, 'decr')">
                                                <button
                                                    :disabled="updateItemProcessing(product.key) || product.quantity < 2"
                                                    type="submit"
                                                    :class="[updateItemProcessing(product.key) || product.quantity < 2 ? 'opacity-50' : 'hover:text-gray-500']"
                                                    class="inline-flex p-2 text-gray-400 "
                                                >
                                                    <MinusIcon class="size-5" aria-hidden="true" />
                                                </button>
                                            </form>
                                            <input disabled :value="product.quantity" type="text" name="count" id="count" class="w-12 text-center block rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                            <form @submit.prevent="updateItem(product, 'incr')">
                                                <button
                                                    :disabled="updateItemProcessing(product.key)"
                                                    type="submit"
                                                    :class="[updateItemProcessing(product.key) ? 'opacity-50' : 'hover:text-gray-500']"
                                                    class="inline-flex p-2 text-gray-400 hover:text-gray-500"
                                                >
                                                    <PlusIcon class="size-5" aria-hidden="true" />
                                                </button>
                                            </form>
                                        </div>

                                        <div class="absolute right-0 top-0">
                                            <form @submit.prevent="removeItem(product.key)">
                                                <button
                                                    :disabled="removeItemProcessing(product.key)"
                                                    type="submit"
                                                    :class="[removeItemProcessing(product.key) ? 'opacity-50' : 'hover:text-gray-500']"
                                                    class="-m-2 inline-flex p-2 text-gray-400"
                                                >
                                                    <span class="sr-only">Remove</span>
                                                    <XMarkIcon class="size-5" aria-hidden="true" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </section>

                <!-- Order summary -->
                <section aria-labelledby="summary-heading" class="mt-16 rounded-lg bg-gray-50 px-4 py-6 sm:p-6 lg:col-span-5 lg:mt-0 lg:p-8">
                    <h2 id="summary-heading" class="text-lg font-medium text-gray-900">{{ i18n.global.t('order_summary') }}</h2>

                    <dl class="mt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-gray-600">{{ i18n.global.t('subtotal') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_items}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                            <dt class="flex items-center text-sm text-gray-600">{{ i18n.global.t('shipping_estimate') }}</dt>
                            <dd class="text-sm font-medium text-gray-900">
                                <span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_shipping}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                            <dt class="text-base font-medium text-gray-900">{{ i18n.global.t('order_total') }}</dt>
                            <dd class="text-base font-medium text-gray-900">
                                <span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_price}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <button
                            :disabled="form.processing || updateItemForm.processing || removeItemForm.processing"
                            :class="{ 'opacity-50': form.processing || updateItemForm.processing || removeItemForm.processing }"
                            type="submit"
                            class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50"
                        >{{ i18n.global.t('checkout') }}</button>
                    </div>
                </section>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, inject, ref, watch } from "vue";
import { createI18n } from 'vue-i18n';

import { Head, useForm, Link, usePage } from "@inertiajs/vue3";
import { PlusIcon, MinusIcon, XMarkIcon } from '@heroicons/vue/20/solid'

import AppLayout from "@/Layouts/AppLayout.vue";

const route = inject('route');

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
        head_title: 'Cart',
        cart: 'Shopping Cart',
        cart_empty: 'Your cart is empty',
        order_summary: 'Order summary',
        subtotal: 'Subtotal',
        shipping_estimate: 'Shipping estimate',
        order_total: 'Order total',
        checkout: 'Checkout'
    },
    pl: {
        head_title: 'Koszyk',
        cart: 'Koszyk',
        cart_empty: 'Twój koszyk jest pusty',
        order_summary: 'Podsumowanie',
        subtotal: 'Wartość',
        shipping_estimate: 'Dostawa',
        order_total: 'Razem do zapłaty',
        checkout: 'Dostawa i płatność'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const removeItemForm = useForm({
    key: null,
});

const updateItemForm = useForm({
    key: null,
    quantity: null,
});

const form = useForm({});

const removeItemIds = ref([]);
const updateItemIds = ref([]);

watch(
    () => props.cart,
    () => {
        removeItemIds.value = [];
        updateItemIds.value = [];
    },
);

const removeItemProcessing = computed(() => (key) => {
    return removeItemIds.value.includes(key);
});

const updateItemProcessing = computed(() => (key) => {
    return updateItemIds.value.includes(key);
});

const removeItem = (key) => {
    removeItemIds.value.push(key);
    removeItemForm.key = key;
    removeItemForm.post(route('cart.remove-item'), {
        errorBag: 'alert',
        preserveScroll: true,
        onError: () => {
            console.log('error');
        },
    });
};

const updateItem = (product, operator) => {
    updateItemIds.value.push(product.key);

    updateItemForm.key = product.key;

    if (operator === 'decr') {
        updateItemForm.quantity = product.quantity - 1;
    }

    if (operator === 'incr') {
        updateItemForm.quantity = product.quantity + 1;
    }

    updateItemForm.post(route('cart.update-item'), {
        errorBag: 'alert',
        preserveScroll: true,
        onError: () => {
            console.log('error');
        }
    });
};

const submit = () => {
    form.get(route('checkout.index'));
};

</script>
