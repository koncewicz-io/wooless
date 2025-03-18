<template>
    <div class="bg-white h-full">
        <Header :cart="cart" :logged="logged"></Header>

        <main
            class="mx-auto max-w-2xl px-4 pb-16 sm:px-6 sm:pb-24 lg:max-w-7xl lg:px-8"
            :class="['min-h-[calc(100%-4rem-1px-2rem-6rem-1px)]']"
        >
            <div v-if="$page.props.errors.alert" class="mt-8 grid grid-cols-1 gap-y-2">
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

            <div class="mt-8">
                <slot />
            </div>
        </main>

        <footer class="border-t border-gray-200 bg-white">
            <div class="h-24 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex items-center">
                <div class="flex-1 text-center text-sm text-gray-500">Copyright &copy; 2025 Wooless</div>
            </div>
        </footer>
    </div>
</template>

<script setup>

import { inject } from 'vue'

import Header from "@/Layouts/AppHeader.vue";

const route = inject('route');

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
</script>
