<template>
    <Head :title="product.name" />

    <AppLayout :cart="cart" :product="product" :logged="logged">

        <div class="pt-8 lg:grid lg:auto-rows-min lg:grid-cols-12 lg:gap-x-8">
            <div class="lg:col-span-5 lg:col-start-8">
                <div class="flex justify-between">
                    <h1 class="text-xl font-medium text-gray-900">{{ product.name }}</h1>
                    <p class="whitespace-nowrap text-xl font-medium text-gray-900"><span v-if="product.prices.currency_prefix" v-html="product.prices.currency_prefix"></span>{{ product.formated_prices.price }}<span v-if="product.prices.currency_suffix" v-html="product.prices.currency_suffix"></span></p>
                </div>
                <!-- Reviews -->
                <div class="mt-4">
                    <h2 class="sr-only">Reviews</h2>
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <StarIcon v-for="rating in [0, 1, 2, 3, 4]" :key="rating" :class="[product.average_rating > rating ? 'text-yellow-400' : 'text-gray-200', 'size-5 shrink-0']" aria-hidden="true" />
                        </div>
                        <p class="ml-1 text-sm text-gray-700">
                            {{ product.average_rating }} ({{ product.review_count }})
                            <span class="sr-only"> out of 5 stars</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Image gallery -->
            <div class="mt-8 lg:col-span-7 lg:col-start-1 lg:row-span-3 lg:row-start-1 lg:mt-0">
                <h2 class="sr-only">Images</h2>

                <div class="grid grid-cols-1">
                    <img
                        v-for="(image, index) in product.images"
                        :key="image.id"
                        :src="image.src"
                        :alt="image.alt || 'Product image'"
                        :srcset="image.srcset"
                        :sizes="image.sizes"
                        :class="[index === 0 ? 'lg:col-span-2 lg:row-span-2' : 'hidden lg:block', 'rounded-lg']"
                    />
                </div>
            </div>

            <div class="mt-8 lg:col-span-5">
                <form @submit.prevent="submit">
                    <button
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                        type="submit"
                        class="mt-8 flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >{{i18n.global.t('add_to_cart')}}</button>
                </form>

                <!-- Product details -->
                <div class="mt-10">
                    <h2 class="text-sm font-medium text-gray-900">{{i18n.global.t('description')}}</h2>

                    <div class="mt-4 space-y-4 text-sm/6 text-gray-500" v-html="product.description" />
                </div>
            </div>
        </div>

        <!-- Reviews -->
        <section v-if="false" aria-labelledby="reviews-heading" class="mt-16 sm:mt-24">
            <h2 id="reviews-heading" class="text-lg font-medium text-gray-900">Recent reviews</h2>

            <div class="mt-6 space-y-10 divide-y divide-gray-200 border-t border-gray-200 pb-10">
                <div v-for="review in reviews.featured" :key="review.id" class="pt-10 lg:grid lg:grid-cols-12 lg:gap-x-8">
                    <div class="lg:col-span-8 lg:col-start-5 xl:col-span-9 xl:col-start-4 xl:grid xl:grid-cols-3 xl:items-start xl:gap-x-8">
                        <div class="flex items-center xl:col-span-1">
                            <div class="flex items-center">
                                <StarIcon v-for="rating in [0, 1, 2, 3, 4]" :key="rating" :class="[review.rating > rating ? 'text-yellow-400' : 'text-gray-200', 'size-5 shrink-0']" aria-hidden="true" />
                            </div>
                            <p class="ml-3 text-sm text-gray-700">{{ review.rating }}<span class="sr-only"> out of 5 stars</span></p>
                        </div>

                        <div class="mt-4 lg:mt-6 xl:col-span-2 xl:mt-0">
                            <h3 class="text-sm font-medium text-gray-900">{{ review.title }}</h3>

                            <div class="mt-3 space-y-6 text-sm text-gray-500" v-html="review.content" />
                        </div>
                    </div>

                    <div class="mt-6 flex items-center text-sm lg:col-span-4 lg:col-start-1 lg:row-start-1 lg:mt-0 lg:flex-col lg:items-start xl:col-span-3">
                        <p class="font-medium text-gray-900">{{ review.author }}</p>
                        <time :datetime="review.datetime" class="ml-4 border-l border-gray-200 pl-4 text-gray-500 lg:ml-0 lg:mt-2 lg:border-0 lg:pl-0">{{ review.date }}</time>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<script setup>

import { inject, ref } from 'vue'
import { createI18n } from 'vue-i18n';

import { StarIcon } from '@heroicons/vue/20/solid'
import { Head, useForm, usePage } from "@inertiajs/vue3";

import AppLayout from "@/Layouts/AppLayout.vue";

const route = inject('route');

const props = defineProps({
    product: {
        type: Object,
    },
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
        add_to_cart: 'Add to cart',
        description: 'Description'
    },
    pl: {
        add_to_cart: 'Do koszyka',
        description: 'Opis'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const form = useForm({
    id: props.product.id,
    quantity: 1,
});

const reviews = {
    average: 3.9,
    totalCount: 512,
    featured: [
        {
            id: 1,
            title: "Can't say enough good things",
            rating: 5,
            content: `
        <p>I was really pleased with the overall shopping experience. My order even included a little personal, handwritten note, which delighted me!</p>
        <p>The product quality is amazing, it looks and feel even better than I had anticipated. Brilliant stuff! I would gladly recommend this store to my friends. And, now that I think of it... I actually have, many times!</p>
      `,
            author: 'Risako M',
            date: 'May 16, 2021',
            datetime: '2021-01-06',
        },
        // More reviews...
    ],
}

const open = ref(false);

const submit = () => {
    form.post(route('cart.add-item'), {
        errorBag: 'alert',
        onError: () => {
            console.log('error');
        },
    });
};

</script>
