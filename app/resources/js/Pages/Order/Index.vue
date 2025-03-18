<template>
    <Head title="Orders" />
    <AppLayout :cart="cart" :logged="logged">
        <div class="bg-white">
            <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                <div class="max-w-xl">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Your Orders</h1>
                    <p v-if="orders.length" class="mt-2 text-sm text-gray-500">Check the status of recent orders, manage returns, and discover similar products.</p>
                    <p v-if="!orders.length" class="mt-2 text-sm text-gray-500">You don't have any orders yet</p>
                </div>

                <div v-if="orders.length" class="mt-12 space-y-16 sm:mt-16">
                    <section v-for="order in orders" :key="order.id" :aria-labelledby="`${order.id}-heading`">
                        <div class="space-y-1 md:flex md:items-baseline md:space-x-4 md:space-y-0">
                            <h2 :id="`${order.id}-heading`" class="text-lg font-medium text-gray-900 md:shrink-0">Order #{{ order.id }}</h2>
                            <div class="space-y-5 sm:flex sm:items-baseline sm:justify-between sm:space-y-0 md:min-w-0 md:flex-1">
                                <p class="text-sm font-medium text-gray-500">{{ order.status }}</p>
                            </div>
                        </div>

                        <div class="-mb-6 mt-6 flow-root divide-y divide-gray-200 border-t border-gray-200">
                            <div v-for="product in order.line_items" :key="product.id" class="py-6 sm:flex">
                                <div class="flex space-x-4 sm:min-w-0 sm:flex-1 sm:space-x-6 lg:space-x-8">
                                    <img :src="product.image?.src" alt="" class="size-20 flex-none rounded-md object-cover sm:size-48" />
                                    <div class="min-w-0 flex-1 pt-1.5 sm:pt-0">
                                        <h3 class="text-sm font-medium text-gray-900">
                                            <Link :href="route('product.show', product.product_id)">{{ product.name }}</Link>
                                        </h3>
                                        <p class="mt-1 font-medium text-gray-900">{{ product.price }}</p>
                                    </div>
                                </div>
                                <div v-if="false" class="mt-6 space-y-4 sm:ml-6 sm:mt-0 sm:w-40 sm:flex-none">
                                    <button type="button" class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-2.5 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-full sm:grow-0">Buy again</button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>

import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    cart: {
        type: Object,
    },
    orders: {
        type: Array,
    },
    logged: {
        type: Boolean,
        default: false
    }
});

</script>
