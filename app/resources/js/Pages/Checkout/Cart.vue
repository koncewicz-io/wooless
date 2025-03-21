<template>
    <!-- Mobile order summary -->
    <section aria-labelledby="order-heading" class="bg-gray-50 px-4 py-6 sm:px-6 lg:hidden">
        <Disclosure as="div" class="mx-auto max-w-lg" v-slot="{ open }">
            <div class="flex items-center justify-between">
                <h2 id="order-heading" class="text-lg font-medium text-gray-900">{{ i18n.global.t('your_order') }}</h2>
                <DisclosureButton class="font-medium text-indigo-600 hover:text-indigo-500">
                    <span v-if="open">{{ i18n.global.t('hide_full_summary') }}</span>
                    <span v-if="!open">{{ i18n.global.t('show_full_summary') }}</span>
                </DisclosureButton>
            </div>

            <DisclosurePanel>
                <ul role="list" class="divide-y divide-gray-200 border-b border-gray-200">
                    <li v-for="product in cart.items" :key="product.id" class="flex space-x-6 py-6">
                        <img
                            :src="product.images[0]?.src"
                            :srcset="product.images[0]?.srcset"
                            :sizes="product.images[0]?.sizes || '(max-width: 640px) 100vw, (max-width: 768px) 50vw, 33vw'"
                            :alt="product.images[0]?.alt"
                            class="size-40 flex-none rounded-md bg-gray-200 object-cover"
                        />
                        <div class="flex flex-col justify-between space-y-4">
                            <div class="space-y-1 text-sm font-medium">
                                <h3 class="text-gray-900">{{ product.name }}</h3>
                                <p class="text-gray-900"><span v-if="product.prices.currency_prefix" v-html="product.prices.currency_prefix"></span>{{ product.formatted_prices.sale_price }}<span v-if="product.prices.currency_suffix" v-html="product.prices.currency_suffix"></span></p>
                                <!--                                        <p class="text-gray-500">{{ product.color }}</p>-->
                                <!--                                        <p class="text-gray-500">{{ product.size }}</p>-->
                            </div>
                            <div class="flex space-x-4">
                                <Link :href="route('cart.index')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ i18n.global.t('edit') }}</Link>
                                <form class="flex border-l border-gray-300 pl-4" @submit.prevent="removeItem(product.key)">
                                    <button
                                        :disabled="removeItemProcessing(product.key)"
                                        type="submit"
                                        :class="[removeItemProcessing(product.key) ? 'opacity-50' : '']"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                    >{{ i18n.global.t('remove') }}</button>
                                </form>
                            </div>
                        </div>
                    </li>
                </ul>

                <form v-if="false" class="mt-10">
                    <label for="discount-code-mobile" class="block text-sm/6 font-medium text-gray-700">Discount code</label>
                    <div class="mt-1 flex space-x-4">
                        <input type="text" id="discount-code-mobile" name="discount-code-mobile" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <button type="submit" class="rounded-md bg-gray-200 px-4 text-sm font-medium text-gray-600 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Apply</button>
                    </div>
                </form>

                <dl class="mt-10 space-y-6 text-sm font-medium text-gray-500">
                    <div class="flex justify-between">
                        <dt>{{ i18n.global.t('subtotal') }}</dt>
                        <dd class="text-gray-900"><span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_items}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span></dd>
                    </div>
                    <div v-if="false" class="flex justify-between">
                        <dt class="flex">
                            Discount
                            <span class="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs tracking-wide text-gray-600">{{ discount.code }}</span>
                        </dt>
                        <dd class="text-gray-900">-{{ discount.amount }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ i18n.global.t('shipping') }}</dt>
                        <dd class="text-gray-900"><span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_shipping}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span></dd>
                    </div>
                </dl>
            </DisclosurePanel>

            <p class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6 text-sm font-medium text-gray-900">
                <span class="text-base">{{ i18n.global.t('total') }}</span>
                <span class="text-base"><span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_price}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span></span>
            </p>
        </Disclosure>
    </section>

    <!-- Order summary -->
    <section aria-labelledby="summary-heading" class="hidden w-full max-w-md flex-col bg-gray-50 lg:flex">
        <h2 id="summary-heading" class="sr-only">Order summary</h2>

        <ul role="list" class="flex-auto divide-y divide-gray-200 overflow-y-auto px-6">
            <li v-for="product in cart.items" :key="product.id" class="flex space-x-6 py-6">
                <img
                    :src="product.images[0]?.src"
                    :srcset="product.images[0]?.srcset"
                    :sizes="product.images[0]?.sizes || '(max-width: 640px) 100vw, (max-width: 768px) 50vw, 33vw'"
                    :alt="product.images[0]?.alt"
                    class="size-40 flex-none rounded-md bg-gray-200 object-cover"
                />
                <div class="flex flex-col justify-between space-y-4">
                    <div class="space-y-1 text-sm font-medium">
                        <h3 class="text-gray-900">{{ product.name }}</h3>
                        <p class="text-gray-900"><span v-if="product.prices.currency_prefix" v-html="product.prices.currency_prefix"></span>{{ product.formatted_prices.sale_price }}<span v-if="product.prices.currency_suffix" v-html="product.prices.currency_suffix"></span></p>
                        <!--                                <p class="text-gray-500">{{ product.color }}</p>-->
                        <!--                                <p class="text-gray-500">{{ product.size }}</p>-->
                    </div>
                    <div class="flex space-x-4">
                        <Link :href="route('cart.index')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ i18n.global.t('edit') }}</Link>
                        <form class="flex border-l border-gray-300 pl-4" @submit.prevent="removeItem(product.key)">
                            <button
                                :disabled="removeItemProcessing(product.key)"
                                type="submit"
                                :class="[removeItemProcessing(product.key) ? 'opacity-50' : '']"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                            >{{ i18n.global.t('remove') }}</button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>

        <div class="sticky bottom-0 flex-none border-t border-gray-200 bg-gray-50 p-6">
            <form v-if="false">
                <label for="discount-code" class="block text-sm/6 font-medium text-gray-700">Discount code</label>
                <div class="mt-1 flex space-x-4">
                    <input type="text" id="discount-code" name="discount-code" class="block w-full rounded-md bg-white px-3 py-2 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                    <button type="submit" class="rounded-md bg-gray-200 px-4 text-sm font-medium text-gray-600 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Apply</button>
                </div>
            </form>

            <dl class="space-y-6 text-sm font-medium text-gray-500">
                <div class="flex justify-between">
                    <dt>{{ i18n.global.t('subtotal') }}</dt>
                    <dd class="text-gray-900"><span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_items}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span></dd>
                </div>
                <div v-if="false" class="flex justify-between">
                    <dt class="flex">
                        Discount
                        <span class="ml-2 rounded-full bg-gray-200 px-2 py-0.5 text-xs tracking-wide text-gray-600">{{ discount.code }}</span>
                    </dt>
                    <dd class="text-gray-900">-{{ discount.amount }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>{{ i18n.global.t('shipping') }}</dt>
                    <dd class="text-gray-900"><span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_shipping}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span></dd>
                </div>
                <div class="flex items-center justify-between border-t border-gray-200 pt-6 text-gray-900">
                    <dt class="text-base">{{ i18n.global.t('total') }}</dt>
                    <dd class="text-base"><span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_price}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span></dd>
                </div>
            </dl>
        </div>
    </section>
</template>

<script setup>

import { computed, inject, ref } from "vue";
import { createI18n } from 'vue-i18n';

import { Link, useForm, usePage } from "@inertiajs/vue3";
import { Disclosure, DisclosureButton, DisclosurePanel } from "@headlessui/vue";

const route = inject('route');

const props = defineProps({
    cart: {
        type: Object
    }
});

const messages = {
    en: {
        checkout: 'Checkout',
        subtotal: 'Subtotal',
        shipping: 'Shipping',
        total: 'Total',
        your_order: 'Your order',
        show_full_summary: 'Show full summary',
        hide_full_summary: 'Hide full summary',
        edit: 'Edit',
        remove: 'Remove'
    },
    pl: {
        checkout: 'Dostawa i płatność',
        subtotal: 'Wartość',
        shipping: 'Dostawa',
        total: 'Razem',
        your_order: 'Zamówienie',
        show_full_summary: 'Pokaż',
        hide_full_summary: 'Ukryj',
        edit: 'Edytuj',
        remove: 'Usuń',
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const removeItemForm = useForm({
    key: null,
});

const removeItemIds = ref([]);

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

const removeItemProcessing = computed(() => (key) => {
    return removeItemIds.value.includes(key);
});

</script>
