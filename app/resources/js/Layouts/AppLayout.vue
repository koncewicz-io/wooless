<template>
    <div class="bg-white h-full">
        <Header :cart="cart" :logged="logged"></Header>

        <main
            class="mx-auto max-w-2xl px-4 pb-16 sm:px-6 sm:pb-24 lg:max-w-7xl lg:px-8"
            :class="['min-h-[calc(100%-4rem-1px-6rem-1px)]']"
        >
            <slot />
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
