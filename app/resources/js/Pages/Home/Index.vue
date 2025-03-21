<template>
    <Head :title="i18n.global.t('head_title')" />

    <AppLayout :cart="cart" :logged="logged">
        <Flicking :class="['pt-8']" :options="{
            circular: true,
            preventDefaultOnDrag: true,
            defaultIndex: activeIndex
        }"
                  @moveStart="onMoveStart"
                  @moveEnd="onMoveEnd"
                  @changed="onChanged"
        >
            <div v-for="(item, index) in [1,2,3,4,5,6,7,8]" :key="index" class="panel">
                <div class="border border-gray-400 border-dashed relative h-64 w-64 ml-1 mr-1 overflow-hidden rounded-md border opacity-75">
                    <svg class="absolute inset-0 size-full stroke-gray-900/10" fill="none">
                        <defs>
                            <pattern id="pattern-5c1e4f0e-62d5-498b-8ff0-cf77bb448c8e" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                            </pattern>
                        </defs>
                        <rect stroke="none" fill="url(#pattern-5c1e4f0e-62d5-498b-8ff0-cf77bb448c8e)" width="100%" height="100%"></rect>
                    </svg>
                </div>
            </div>
        </Flicking>

        <div class="overflow-hidden ">
            <div class="mt-6 pb-6 pt-6 border-b border-t border-gray-200">
                <div class="relative h-16 overflow-hidden rounded-md border border-dashed border-gray-400 opacity-75">
                    <svg class="absolute inset-0 size-full stroke-gray-900/10" fill="none">
                        <defs>
                            <pattern id="pattern-0ceb5d81-6b62-482c-a66b-f68e73fb5548" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                            </pattern>
                        </defs>
                        <rect stroke="none" fill="url(#pattern-0ceb5d81-6b62-482c-a66b-f68e73fb5548)" width="100%" height="100%"></rect>
                    </svg>
                </div>
            </div>
            <div class="mt-6">
                <div class="relative h-64 overflow-hidden rounded-md border border-dashed border-gray-400 opacity-75">
                    <svg class="absolute inset-0 size-full stroke-gray-900/10" fill="none">
                        <defs>
                            <pattern id="pattern-c4a401f9-60f2-4f88-a492-cefe73552092" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                            </pattern>
                        </defs>
                        <rect stroke="none" fill="url(#pattern-c4a401f9-60f2-4f88-a492-cefe73552092)" width="100%" height="100%"></rect>
                    </svg>
                </div>
            </div>
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="relative h-16 overflow-hidden rounded-md border border-dashed border-gray-400 opacity-75">
                    <svg class="absolute inset-0 size-full stroke-gray-900/10" fill="none">
                        <defs>
                            <pattern id="pattern-d7f13698-f273-417e-9e21-6f736f6dfe37" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3"></path>
                            </pattern>
                        </defs>
                        <rect stroke="none" fill="url(#pattern-d7f13698-f273-417e-9e21-6f736f6dfe37)" width="100%" height="100%"></rect>
                    </svg>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { createI18n } from 'vue-i18n';

import Flicking from "@egjs/vue3-flicking";
import { Head, usePage } from "@inertiajs/vue3";
import "@egjs/vue3-flicking/dist/flicking.css";

import AppLayout from "@/Layouts/AppLayout.vue";

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
        head_title: 'Home'
    },
    pl: {
        head_title: 'Główna'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const activeIndex = ref(0);
const isMoving = ref(false);

const onMoveStart = (e) => {
    console.log("moveStart:" + e.currentTarget.index);
    isMoving.value = true;
};

const onMoveEnd = (e) => {
    console.log("moveEnd:" + e.currentTarget.index);
    activeIndex.value = e.currentTarget.index;
    isMoving.value = false;
};

const onChanged = (e) => {
    console.log("changed:" + e.currentTarget.index);
    activeIndex.value = e.currentTarget.index;
};

</script>
