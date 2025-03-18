<template>
    <Head :title="i18n.global.t('head_title')" />

    <AppLayout :cart="cart" :logged="logged">
        <TransitionRoot as="template" :show="mobileFiltersOpen">
            <Dialog class="relative z-40 lg:hidden" @close="mobileFiltersOpen = false">
                <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-black/25" />
                </TransitionChild>

                <div class="fixed inset-0 z-40 flex">
                    <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="translate-x-full">
                        <DialogPanel class="relative ml-auto flex size-full max-w-xs flex-col overflow-y-auto bg-white py-4 pb-6 shadow-xl">
                            <div class="flex items-center justify-between px-4">
                                <h2 class="text-lg font-medium text-gray-900">{{ i18n.global.t('filters') }}</h2>
                                <button type="button" class="relative -mr-2 flex size-10 items-center justify-center p-2 text-gray-400 hover:text-gray-500" @click="mobileFiltersOpen = false">
                                    <span class="absolute -inset-0.5" />
                                    <span class="sr-only">Close menu</span>
                                    <XMarkIcon class="size-6" aria-hidden="true" />
                                </button>
                            </div>

                            <!-- Filters -->
                            <form class="mt-4">
                                <Disclosure as="div" v-for="section in filters" :key="section.name" class="border-t border-gray-200 pb-4 pt-4" v-slot="{ open }">
                                    <fieldset>
                                        <legend class="w-full px-2">
                                            <DisclosureButton class="flex w-full items-center justify-between p-2 text-gray-400 hover:text-gray-500">
                                                <span class="text-sm font-medium text-gray-900">{{ section.name }}</span>
                                                <span class="ml-6 flex h-7 items-center">
                                                    <ChevronDownIcon :class="[open ? '-rotate-180' : 'rotate-0', 'size-5 transform']" aria-hidden="true" />
                                                </span>
                                            </DisclosureButton>
                                        </legend>
                                        <DisclosurePanel class="px-4 pb-2 pt-4">
                                            <div class="space-y-6">
                                                <div v-for="(option, optionIdx) in section.options" :key="option.id" class="flex gap-3">
                                                    <div class="flex h-5 shrink-0 items-center">
                                                        <div class="group grid size-4 grid-cols-1">
                                                            <input @change="filterProducts(section.id, option)" :checked="option.active" :id="`${section.id}-${optionIdx}-mobile`" :name="`${section.id}[]`" :value="option.id" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                                            <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                                                <path class="opacity-0 group-has-[:checked]:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                                <path class="opacity-0 group-has-[:indeterminate]:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <label :for="`${section.id}-${optionIdx}-mobile`" class="text-sm text-gray-500">{{ option.name }}</label>
                                                </div>
                                            </div>
                                        </DisclosurePanel>
                                    </fieldset>
                                </Disclosure>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>

        <div class="border-b border-gray-200 pb-10 pt-16">
            <div class="max-w-3xl">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900">{{ i18n.global.t('style_and_comfort') }}</h1>
                <p class="mt-4 text-base text-gray-500">{{ i18n.global.t('style_and_comfort_description') }}</p>
            </div>
        </div>

        <div class="pb-24 pt-12 lg:grid lg:grid-cols-3 lg:gap-x-8 xl:grid-cols-4">
            <aside>
                <h2 class="sr-only">{{ i18n.global.t('filters') }}</h2>

                <button type="button" class="inline-flex items-center lg:hidden" @click="mobileFiltersOpen = true">
                    <span class="text-sm font-medium text-gray-700">{{ i18n.global.t('filters') }}</span>
                    <PlusIcon class="ml-1 size-5 shrink-0 text-gray-400" aria-hidden="true" />
                </button>

                <div class="hidden lg:block">
                    <form class="space-y-10 divide-y divide-gray-200">
                        <div v-for="(section, sectionIdx) in filters" :key="section.name" :class="sectionIdx === 0 ? null : 'pt-10'">
                            <fieldset>
                                <legend class="block text-sm font-medium text-gray-900">{{ section.name }}</legend>
                                <div class="space-y-3 pt-6">
                                    <div v-for="(option, optionIdx) in section.options" :key="option.id" class="flex gap-3">
                                        <div class="flex h-5 shrink-0 items-center">
                                            <div class="group grid size-4 grid-cols-1">
                                                <input @change="filterProducts(section.id, option)" :checked="option.active" :id="`${section.id}-${optionIdx}`" :name="`${section.id}[]`" :value="option.id" type="checkbox" class="col-start-1 row-start-1 appearance-none rounded border border-gray-300 bg-white checked:border-indigo-600 checked:bg-indigo-600 indeterminate:border-indigo-600 indeterminate:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                                <svg class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-[:disabled]:stroke-gray-950/25" viewBox="0 0 14 14" fill="none">
                                                    <path class="opacity-0 group-has-[:checked]:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    <path class="opacity-0 group-has-[:indeterminate]:opacity-100" d="M3 7H11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </div>
                                        </div>
                                        <label :for="`${section.id}-${optionIdx}`" class="text-sm text-gray-600">{{ option.name }}</label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </form>
                </div>
            </aside>

            <section aria-labelledby="product-heading" class="mt-6 lg:col-span-2 lg:mt-0 xl:col-span-3">
                <h2 id="product-heading" class="sr-only">Products</h2>

                <div class="grid grid-cols-1 items-start gap-x-6 gap-y-16 sm:grid-cols-2">
                    <div v-for="post in posts" :key="post.title" class="flex flex-col-reverse">
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-gray-900">{{ post.title }}</h3>
                            <p class="mt-2 text-sm text-gray-500" v-html="post.shortDescription"></p>
                        </div>
                        <img :src="post.imageSrc" class="aspect-square w-full rounded-lg bg-gray-100 object-cover" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-6 sm:gap-y-10 lg:gap-x-8 xl:grid-cols-3">
                    <Link :href="route('product.show', product.id)" v-for="product in products" :key="product.id" class="group relative flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white">
                        <img :src="product.images[0]?.src" :alt="product.imageAlt" class="aspect-[3/4] bg-gray-200 object-cover group-hover:opacity-75 sm:h-96" />
                        <div class="flex flex-1 flex-col space-y-2 p-4">
                            <h3 class="text-sm font-medium text-gray-900">
                                <a :href="product.href">
                                    <span aria-hidden="true" class="absolute inset-0" />
                                    {{ product.name }}
                                </a>
                            </h3>
                            <div class="flex flex-1 flex-col justify-end">
                                <p class="text-sm italic text-gray-500">{{ product.options }}</p>
                                <p class="text-base font-medium text-gray-900">{{ product.price }}</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>

import { inject, ref } from 'vue'
import { createI18n } from 'vue-i18n';

import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
    Dialog,
    DialogPanel,
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue'

import { XMarkIcon } from '@heroicons/vue/24/outline'
import { ChevronDownIcon, PlusIcon } from '@heroicons/vue/20/solid'

import AppLayout from "@/Layouts/AppLayout.vue";

const route = inject('route');

const props = defineProps({
    posts: {
        type: Array,
    },
    filters: {
        type: Array,
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
        head_title: 'Blog',
        style_and_comfort: 'Style and comfort',
        style_and_comfort_description: 'Choose your favorite look and enjoy everyday comfort!',
        filters: 'Filters'
    },
    pl: {
        head_title: 'Blog',
        style_and_comfort: 'Styl i wygoda',
        style_and_comfort_description: 'Wybierz swój ulubiony styl i ciesz się komfortem każdego dnia!',
        filters: 'Filtry'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const open = ref(false);
const mobileFiltersOpen = ref(false)

const filterProducts = (sectionId, option) => {
    const data = {};

    props.filters.forEach(section => {
        const activeOptions = section.options.filter(opt => opt.active);
        const isCurrentSection = section.id === sectionId;

        if (isCurrentSection && option.active) {
            data[section.id] = activeOptions
                .filter(opt => opt.id !== option.id)
                .map(opt => opt.id);
            return;
        }

        if (isCurrentSection) {
            data[section.id] = [...activeOptions.map(opt => opt.id), option.id];
            return;
        }

        data[section.id] = activeOptions.map(opt => opt.id);
    });

    router.visit(route('post.index'), {
        method: 'get',
        preserveScroll: true,
        data,
    });
};
</script>
