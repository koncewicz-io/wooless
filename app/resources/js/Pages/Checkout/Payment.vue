<template>
    <Head :title="i18n.global.t('head_title')" />

    <CheckoutLayout>
        <TransitionRoot as="template" :show="open">
            <Dialog class="relative z-10" @close="open = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center items-center p-0">
                        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 translate-y-0 scale-95" enter-to="opacity-100 translate-y-0 scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 scale-100" leave-to="opacity-0 translate-y-4 translate-y-0 scale-95">
                            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all my-8 w-full max-w-sm p-6">
                                <div>
                                    <DialogTitle as="h2" class="text-lg font-medium text-gray-900">{{ i18n.global.t('select_option') }}</DialogTitle>
                                    <div class="mt-6">
                                        <fieldset>
                                            <legend class="text-sm/6 font-semibold text-gray-900">{{ i18n.global.t('online_bank_transfer') }}</legend>
                                            <div class="mt-6 space-y-3">
                                                <div @click="selectBank(bank)" v-for="bank in banks" :key="bank.id" class="flex items-center">
                                                    <input
                                                        v-model="selectedBank"
                                                        :value="bank.id"
                                                        :id="bank.id"
                                                        name="bank"
                                                        type="radio"
                                                        class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden"
                                                    />
                                                    <label :for="bank.id" class="ml-3 block text-sm/6 text-gray-900">{{ bank.name }}</label>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <div class="lg:flex lg:min-h-full lg:flex-row-reverse lg:overflow-hidden">
            <Cart :cart="cart"></Cart>

            <!-- Checkout form -->
            <section class="flex-auto overflow-y-auto px-4 pb-16 pt-12 sm:px-6 sm:pt-16 lg:px-8 lg:pb-24 lg:pt-8">
                <div class="mx-auto max-w-lg">
                    <form @submit.prevent="submit">
                        <div v-if="$page.props.errors.exception" class="pb-8 grid grid-cols-1 gap-y-2">
                            <div class="rounded-md bg-red-50 p-4"
                            >
                                <div class="flex">
                                    <div class="shrink-0">
                                        <svg class="size-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">{{$page.props.errors.exception}}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <RadioGroup v-model="selectedPaymentMethod" :disabled="form.processing" class="space-y-4">
                                <RadioGroupOption
                                    as="template"
                                    @click="selectPaymentMethod(method)"
                                    v-for="method in paymentMethods"
                                    :disabled="method.id === 'bank-transfer' && !method.bank"
                                    :key="method.name"
                                    :value="method"
                                    :aria-label="method.name" v-slot="{ active, checked }"
                                >
                                    <div :class="['select-none border-gray-300 relative block cursor-pointer rounded-lg border bg-white px-6 h-16 shadow-sm focus:outline-none flex justify-between']">
                                        <span class="flex items-center">
                                            <span class="flex flex-col text-sm">
                                                <span class="font-medium text-gray-900">{{ method.name }}</span>
                                                <span v-if="method.id === 'bank-transfer'" class="text-gray-500">
                                                    <span class="block inline">{{ method.bank }}</span>
                                                </span>
                                            </span>
                                        </span>
                                        <span v-if="method.id === 'bank-transfer' && method.bank !== null" class="flex items-center">
                                            <span class="text-sm font-medium text-indigo-600">{{ i18n.global.t('change') }}</span>
                                        </span>
                                        <span :class="[checked ? 'border-indigo-600' : 'border-transparent', 'border-2 pointer-events-none absolute -inset-px rounded-lg']" aria-hidden="true" />
                                    </div>
                                </RadioGroupOption>
                            </RadioGroup>
                        </div>

                        <button
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                            type="submit"
                            class="mt-6 w-full rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >{{ i18n.global.t('pay') }} <span v-if="cart.totals.currency_prefix" v-html="cart.totals.currency_prefix"></span>{{cart.formatted_totals.total_price}}<span v-if="cart.totals.currency_suffix" v-html="cart.totals.currency_suffix"></span></button>

                        <p class="mt-6 flex justify-center text-sm font-medium text-gray-500">
                            <LockClosedIcon class="mr-1.5 size-5 text-gray-400" aria-hidden="true" />
                            {{ i18n.global.t('pay_description') }}
                        </p>
                    </form>
                </div>
            </section>
        </div>
    </CheckoutLayout>
</template>

<script setup>

import { ref } from "vue";
import { createI18n } from 'vue-i18n';

import {
    RadioGroup,
    RadioGroupOption,
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot
} from '@headlessui/vue'

import { LockClosedIcon, XCircleIcon } from "@heroicons/vue/20/solid";

import { Head, useForm, usePage } from "@inertiajs/vue3";

import CheckoutLayout from "@/Layouts/CheckoutLayout.vue"
import Cart from "@/Pages/Checkout/Cart.vue"

const props = defineProps({
    cart: {
        type: Object
    }
});

const messages = {
    en: {
        head_title: 'Payment',
        pay: 'Pay',
        pay_description: 'You will be redirected to the payment gateway',
        bank_transfer: 'Bank transfer',
        select_option: 'Select an option',
        online_bank_transfer: 'Online bank transfer',
        change: 'Change'
    },
    pl: {
        head_title: 'Płatność',
        pay: 'Kupuję i płacę',
        pay_description: 'Zostaniesz przekierowany do bramki płatności',
        bank_transfer: 'Przelew',
        select_option: 'Wybierz opcję',
        online_bank_transfer: 'Przelew online z banku',
        change: 'Zmień'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const form = useForm({
    payment_method: null
});

const paymentMethods = ref([
    { id: 'blik', name: 'Blik', bank: null },
    { id: 'bank-transfer', name: i18n.global.t('bank_transfer'), bank: null }
]);

const banks = ref([
    { id: 'ing', name: 'ING Bank Śląski' },
    { id: 'mbank', name: 'mBank' },
    { id: 'pko', name: 'PKO Bank Polski' },
    { id: 'santander', name: 'Santander Bank Polska' },
])
const selectedBank = ref(null);

const open = ref(false)
const selectedPaymentMethod = ref(paymentMethods.value[0])

const submit = () => {
    form.payment_method = selectedPaymentMethod.value.id;

    if (selectedPaymentMethod.value.id === 'bank-transfer') {
        form.payment_method = selectedBank.value;
    }

    form.post(route('checkout.process'));
};

const selectPaymentMethod = (method) => {
    if (form.processing) {
        return;
    }

    if (method.id === 'bank-transfer') {
        open.value = true;
    }

    if (method.id !== 'bank-transfer') {
        paymentMethods.value[1].bank = null;
        selectedBank.value = null;
    }
}

const selectBank = (bank) => {
    paymentMethods.value[1].bank = bank.name;
    selectedPaymentMethod.value = paymentMethods.value[1];
    selectedBank.value = bank.id;
    open.value = false;
}

</script>
