<template>
    <Head :title="i18n.global.t('head_title') + ' #' + orderId" />

    <AppLayout :cart="cart" :logged="logged">
        <div class="bg-white">
            <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                <div class="max-w-xl">
                    <h1 class="text-base font-medium text-indigo-600">{{ i18n.global.t('thank_you') }}</h1>
                    <p class="mt-2 text-4xl font-bold tracking-tight">{{ i18n.global.t('confirmed') }}</p>
                    <p class="mt-2 text-base text-gray-500">{{ i18n.global.t('your_order') }} #{{orderId}} {{ i18n.global.t('has_been_received') }}</p>
                </div>

                <section v-if="!orderRejected" aria-labelledby="order-heading" class="mt-10 border-t border-gray-200">
                    <h2 id="order-heading" class="sr-only">Your order</h2>

                    <h3 class="sr-only">Items</h3>
                    <div v-for="product in order.items" :key="product.id" class="flex space-x-6 border-b border-gray-200 py-10">
                        <img
                            :src="product.images[0]?.src"
                            :srcset="product.images[0]?.srcset"
                            :sizes="product.images[0]?.sizes || '(max-width: 640px) 100vw, (max-width: 768px) 50vw, 33vw'"
                            :alt="product.images[0]?.alt"
                            class="size-20 flex-none rounded-lg bg-gray-100 object-cover sm:size-40"
                        />
                        <div class="flex flex-auto flex-col">
                            <div>
                                <h4 class="font-medium text-gray-900">
                                    <a :href="product.href">{{ product.name }}</a>
                                </h4>
                                <p class="mt-2 text-sm text-gray-600" v-html="product.short_description"></p>
                            </div>
                            <div class="mt-6 flex flex-1 items-end">
                                <dl class="flex divide-x divide-gray-200 text-sm">
                                    <div class="flex pr-4 sm:pr-6">
                                        <dt class="font-medium text-gray-900">{{ i18n.global.t('quantity') }}</dt>
                                        <dd class="ml-2 text-gray-700">{{ product.quantity }}</dd>
                                    </div>
                                    <div class="flex pl-4 sm:pl-6">
                                        <dt class="font-medium text-gray-900">{{ i18n.global.t('price') }}</dt>
                                        <dd class="ml-2 text-gray-700"><span v-if="product.prices.currency_prefix" v-html="product.prices.currency_prefix"></span>{{ product.formatted_prices.price }}<span v-if="product.prices.currency_suffix" v-html="product.prices.currency_suffix"></span></dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div v-if="!orderRejected" class="sm:ml-40 sm:pl-6">
                        <h3 class="sr-only">Your information</h3>

                        <h4 class="sr-only">Addresses</h4>
                        <dl class="grid grid-cols-2 gap-x-6 py-10 text-sm">
                            <div>
                                <dt class="font-medium text-gray-900">{{ i18n.global.t('shipping_address') }}</dt>
                                <dd class="mt-2 text-gray-700">
                                    <address class="not-italic">
                                        <span class="block">{{order.shipping_address.first_name}} {{order.shipping_address.last_name}}</span>
                                        <span class="block">{{order.shipping_address.country}}</span>
                                        <span class="block">{{order.shipping_address.postcode}}, {{order.shipping_address.city}}</span>
                                        <span class="block">{{order.shipping_address.address_1}}</span>
                                    </address>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900">{{ i18n.global.t('billing_address') }}</dt>
                                <dd class="mt-2 text-gray-700">
                                    <address class="not-italic">
                                        <span class="block">{{order.billing_address.first_name}} {{order.billing_address.last_name}}</span>
                                        <span class="block">{{order.billing_address.country}}</span>
                                        <span class="block">{{order.billing_address.postcode}}, {{order.billing_address.city}}</span>
                                        <span class="block">{{order.billing_address.address_1}}</span>
                                    </address>
                                </dd>
                            </div>
                        </dl>

                        <dl v-if="false" class="grid grid-cols-2 gap-x-6 border-t border-gray-200 py-10 text-sm">
                            <div>
                                <dt class="font-medium text-gray-900">Payment method</dt>
                                <dd class="mt-2 text-gray-700">
                                    <p>Przelewy24</p>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-900">Shipping method</dt>
                                <dd class="mt-2 text-gray-700">
                                    <p>XXX</p>
                                </dd>
                            </div>
                        </dl>

                        <dl class="space-y-6 border-t border-gray-200 pt-10 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-900">{{ i18n.global.t('subtotal') }}</dt>
                                <dd class="text-gray-700"><span v-if="order.totals.currency_prefix" v-html="order.totals.currency_prefix"></span>{{order.formatted_totals.total_items}}<span v-if="order.totals.currency_suffix" v-html="order.totals.currency_suffix"></span></dd>
                            </div>
                            <div v-if="false" class="flex justify-between">
                                <dt class="flex font-medium text-gray-900">
                                    Discount
                                    <span class="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs text-gray-600">STUDENT50</span>
                                </dt>
                                <dd class="text-gray-700">-$18.00 (50%)</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-900">{{ i18n.global.t('shipping') }}</dt>
                                <dd class="text-gray-700"><span v-if="order.totals.currency_prefix" v-html="order.totals.currency_prefix"></span>{{order.formatted_totals.total_shipping}}<span v-if="order.totals.currency_suffix" v-html="order.totals.currency_suffix"></span></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-900">{{ i18n.global.t('total') }}</dt>
                                <dd class="text-gray-900"><span v-if="order.totals.currency_prefix" v-html="order.totals.currency_prefix"></span>{{order.formatted_totals.total_price}}<span v-if="order.totals.currency_suffix" v-html="order.totals.currency_suffix"></span></dd>
                            </div>
                        </dl>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>

import { createI18n } from 'vue-i18n';

import { Head, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    order: {
        type: Object,
    },
    cart: {
        type: Object,
    },
    logged: {
        type: Boolean,
        default: false
    },
    orderId: {
        type: String
    },
    orderRejected: {
        type: Boolean,
        default: false
    },
    orderRejectedCode: {
        type: String,
        default: null
    }
});

const messages = {
    en: {
        head_title: 'Order',
        thank_you: 'Thank you!',
        confirmed: 'It’s confirmed!',
        your_order: 'Your order',
        has_been_received: 'has been received and will be shipped soon.',
        quantity: 'Quantity',
        price: 'Price',
        shipping_address: 'Shipping address',
        billing_address: 'Billing address',
        subtotal: 'Subtotal',
        shipping: 'Shipping',
        total: 'Total'
    },
    pl: {
        head_title: 'Zamówienie',
        thank_you: 'Dziękujemy!',
        confirmed: 'Zamówienie złożone!',
        your_order: 'Twoje zamówienie',
        has_been_received: 'zostało otrzymane i wkrótce zostanie wysłane.',
        quantity: 'Ilość',
        price: 'Cena',
        shipping_address: 'Adres wysyłkowy',
        billing_address: 'Adres rozliczeniowy',
        subtotal: 'Wartość',
        shipping: 'Dostawa',
        total: 'Razem'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

</script>
