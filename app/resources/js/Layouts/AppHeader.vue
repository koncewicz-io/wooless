<template>
    <TransitionRoot as="template" :show="open">
        <Dialog class="relative z-40 lg:hidden" @close="open = false">
            <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/25" />
            </TransitionChild>

            <div class="fixed inset-0 z-40 flex">
                <TransitionChild as="template" enter="transition ease-in-out duration-300 transform" enter-from="-translate-x-full" enter-to="translate-x-0" leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0" leave-to="-translate-x-full">
                    <DialogPanel class="relative flex w-full max-w-xs flex-col overflow-y-auto bg-white pb-12 shadow-xl">
                        <div class="flex px-4 pb-2 pt-5">
                            <button type="button" class="-m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400" @click="open = false">
                                <span class="sr-only">Close menu</span>
                                <XMarkIcon class="size-6" aria-hidden="true" />
                            </button>
                        </div>

                        <div class="space-y-6 border-t border-gray-200 px-4 py-6">
                            <div v-for="page in navigation.pages" :key="page.name" class="flow-root">
                                <Link :href="page.href" class="-m-2 block p-2 font-medium text-gray-900">{{ page.name }}</Link>
                            </div>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>

    <header class="relative bg-white">
        <nav aria-label="Top" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="border-b border-gray-200">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex flex-1 items-center lg:hidden">
                        <button type="button" class="-ml-2 rounded-md bg-white p-2 text-gray-400" @click="open = true">
                            <span class="sr-only">Open menu</span>
                            <Bars3Icon class="size-6" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Flyout menus -->
                    <PopoverGroup class="hidden lg:block lg:flex-1 lg:self-stretch">
                        <div class="flex h-full space-x-8">
                            <Link v-for="page in navigation.pages" :key="page.name" :href="page.href" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-800">{{ page.name }}</Link>
                        </div>
                    </PopoverGroup>

                    <!-- Logo -->
                    <Link :href="route('home.index')" class="flex">
                        <span class="sr-only">Your Company</span>
                        <svg class="h-8 w-auto" viewBox="0 0 47 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#4f46e5" d="M23.5 6.5C17.5 6.5 13.75 9.5 12.25 15.5C14.5 12.5 17.125 11.375 20.125 12.125C21.8367 12.5529 23.0601 13.7947 24.4142 15.1692C26.6202 17.4084 29.1734 20 34.75 20C40.75 20 44.5 17 46 11C43.75 14 41.125 15.125 38.125 14.375C36.4133 13.9471 35.1899 12.7053 33.8357 11.3308C31.6297 9.09158 29.0766 6.5 23.5 6.5ZM12.25 20C6.25 20 2.5 23 1 29C3.25 26 5.875 24.875 8.875 25.625C10.5867 26.0529 11.8101 27.2947 13.1642 28.6693C15.3702 30.9084 17.9234 33.5 23.5 33.5C29.5 33.5 33.25 30.5 34.75 24.5C32.5 27.5 29.875 28.625 26.875 27.875C25.1633 27.4471 23.9399 26.2053 22.5858 24.8307C20.3798 22.5916 17.8266 20 12.25 20Z"/>
                        </svg>
                    </Link>

                    <div class="flex flex-1 items-center justify-end">
                        <div class="lg:ml-4">
                            <Menu v-if="logged" as="div" class="relative inline-block text-left p-2">
                                <div>
                                    <MenuButton class="flex items-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none">
                                        <span class="sr-only">Open options</span>
                                        <UserIcon class="size-6" aria-hidden="true" />
                                    </MenuButton>
                                </div>

                                <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                    <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                                        <div class="py-1">
                                            <MenuItem v-slot="{ active }">
                                                <Link :href="route('order.index')" :class="[active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 'block px-4 py-2 text-sm']">{{ i18n.global.t('orders') }}</Link>
                                            </MenuItem>
                                            <form @submit.prevent="logout">
                                                <MenuItem v-slot="{ active }">
                                                    <button type="submit" :class="[active ? 'bg-gray-100 text-gray-900 outline-none' : 'text-gray-700', 'block w-full px-4 py-2 text-left text-sm']">{{ i18n.global.t('logout') }}</button>
                                                </MenuItem>
                                            </form>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </div>

                        <!-- Account -->
                        <Link v-if="!logged" :href="route('account.index')" class="p-2 text-gray-400 hover:text-gray-500 lg:ml-4">
                            <span class="sr-only">Account</span>
                            <UserIcon class="size-6" aria-hidden="true" />
                        </Link>

                        <!-- Cart -->
                        <div class="ml-4 flow-root lg:ml-6">
                            <Link :href="route('cart.index')" class="group -m-2 flex items-center p-2">
                                <ShoppingBagIcon class="size-6 shrink-0 text-gray-400 group-hover:text-gray-500" aria-hidden="true" />
                                <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-gray-800">{{ cart ? cart.items_count : 0 }}</span>
                                <span class="sr-only">items in cart, view bag</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>
</template>

<script setup>

import { inject, ref } from "vue";
import { createI18n } from 'vue-i18n';
import {Link, router, usePage} from "@inertiajs/vue3";
import {
    Dialog, DialogPanel,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    PopoverGroup,
    TransitionChild,
    TransitionRoot
} from "@headlessui/vue";
import { Bars3Icon, ShoppingBagIcon, UserIcon, XMarkIcon } from "@heroicons/vue/24/outline";

const route = inject('route');
const open = ref(false);

const props = defineProps({
    cart: {
        type: Object,
        default: null
    },
    logged: {
        type: Boolean,
        default: false
    }
});

const messages = {
    en: {
        products: 'Products',
        blog: 'Blog',
        orders: 'Orders',
        logout: 'Sign out'
    },
    pl: {
        products: 'Produkty',
        blog: 'Blog',
        orders: 'Zamówienia',
        logout: 'Wyloguj się'
    }
};

const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const navigation = {
    pages: [
        { name: i18n.global.t('products'), href: route('product.index') },
        { name: i18n.global.t('blog'), href: route('post.index') }
    ],
}

const logout = () => {
    router.visit(route('logout.store'), {
        method: 'post'
    })
};

</script>
