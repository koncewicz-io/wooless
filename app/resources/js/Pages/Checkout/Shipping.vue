<template>
    <Head :title="i18n.global.t('head_title')" />

    <CheckoutLayout>
        <div class="lg:flex lg:min-h-full lg:flex-row-reverse lg:overflow-hidden">
            <Cart :cart="cart"></Cart>
            <section class="flex-auto overflow-y-auto px-4 pb-16 pt-12 sm:px-6 sm:pt-16 lg:px-8 lg:pb-24 lg:pt-0">
                <div class="mx-auto max-w-lg">
                    <form @submit.prevent="submit(false)" class="mt-8">
                        <div v-if="isLoading" class="mt-6">
                            <div class="w-full">
                                <div class="animate-pulse flex space-x-3">
                                    <div class="rounded-full bg-slate-200 h-4 w-4"></div>
                                    <div class="flex-1 space-y-3 py-1">
                                        <div class="space-y-3">
                                            <div class="grid grid-cols-5 gap-4">
                                                <div class="h-2 bg-slate-200 rounded col-span-4"></div>
                                                <div class="h-2 bg-slate-200 rounded col-span-1"></div>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="grid grid-cols-3 gap-4">
                                                <div class="h-2 bg-slate-200 rounded col-span-2"></div>
                                                <div class="h-2 bg-slate-200 rounded col-span-1"></div>
                                            </div>
                                            <div class="h-2 bg-slate-200 rounded"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!isLoading" class="mt-6">
                            <fieldset>
                                <legend class="text-sm/6 font-semibold text-gray-900">{{ i18n.global.t('pickup_points') }}</legend>
                            </fieldset>

                            <div class="mt-2.5 divide-y divide-gray-200">
                                <p v-if="!points.length" class="mt-1 text-sm text-gray-500">{{ i18n.global.t('no_pickup_points_available') }}</p>
                                <div v-for="(point, index) in points" :key="index" class="relative flex items-start pb-4 pt-3.5">
                                    <div class="flex h-6 items-center">
                                        <input
                                            :disabled="form.processing"
                                            v-model="selectedPickupPointCode"
                                            :value="{
                                                package_id: point.shipping_rate.package_id,
                                                rate_id: point.shipping_rate.rate_id
                                            }"
                                            :id="'pickup_' + index"
                                            :aria-describedby="'pickup_' + index + '_description'"
                                            name="delivery_method"
                                            type="radio"
                                            class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden"
                                        />
                                    </div>
                                    <div class="ml-3 min-w-0 flex-1 text-sm/6">
                                        <label v-if="point.shipping_rate" :for="'pickup_' + index" class="font-medium text-gray-900" v-html="point.shipping_rate.name"></label>
                                        <label v-else :for="'pickup_' + index" class="font-medium text-gray-900" v-html="point.point_type_str"></label>

                                        <p :id="'pickup_' + index + '_description'" class="text-xs/6 text-gray-500">{{ point.address.street }}</p>
                                        <p class="font-medium text-gray-900">{{ i18n.global.t('at_your_place') }}</p>
                                        <p v-if="selectedPickupPointCode.package_id === point.shipping_rate.package_id && selectedPickupPointCode.rate_id === point.shipping_rate.rate_id" class="text-xs/6 text-gray-500">{{ Math.round(point.distance * 1000) }} m {{ i18n.global.t('from_address') }}</p>
                                        <p
                                            @click="() => {
                                                selectedPoint = point;
                                                selectedPointIndex = index;
                                                showMap = true;
                                            }"
                                            class="mt-2 text-indigo-600 hover:text-indigo-500 cursor-pointer"
                                        >
                                            <span v-if="selectedPickupPointCode.package_id === point.shipping_rate.package_id && selectedPickupPointCode.rate_id === point.shipping_rate.rate_id">{{ i18n.global.t('change_pickup_point') }}</span>
                                            <span v-if="selectedPickupPointCode.package_id !== point.shipping_rate.package_id || selectedPickupPointCode.rate_id !== point.shipping_rate.rate_id">{{ Math.round(point.distance * 1000) }} m {{ i18n.global.t('from_address') }} - {{ i18n.global.t('map') }}</span>
                                        </p>
                                    </div>
                                    <div class="flex">
                                        <span v-if="point.shipping_rate" class="font-medium text-gray-900"><span v-if="point.shipping_rate.currency_prefix" v-html="point.shipping_rate.currency_prefix"></span>{{point.shipping_rate.formated_price}}<span v-if="point.shipping_rate.currency_suffix" v-html="point.shipping_rate.currency_suffix"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!isLoading" class="mt-6">
                            <fieldset>
                                <legend class="text-sm/6 font-semibold text-gray-900">{{ i18n.global.t('delivery_to_address') }}</legend>
                                <div class="mt-5">

                                    <div v-for="(rate, index) in cart.shipping_rates" :key="index">
                                        <template v-for="(item, subIndex) in rate.shipping_rates">
                                            <div
                                                :key="subIndex"
                                                class="relative flex items-start pb-2 pt-1"
                                                v-if="!furgonetkaDeliveryToType[item.rate_id]"
                                            >
                                                <div class="flex h-6 items-center">
                                                    <input
                                                        :disabled="form.processing"
                                                        v-model="selectedPickupPointCode"
                                                        :value="{
                                                            package_id: rate.package_id,
                                                            rate_id: item.rate_id
                                                        }"
                                                        :id="`courier_` + item.rate_id"
                                                        :aria-describedby="`courier_` + item.rate_id + `_description`"
                                                        name="delivery_method"
                                                        type="radio"
                                                        class="relative size-4 appearance-none rounded-full border border-gray-300 bg-white before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden"
                                                    />
                                                </div>
                                                <div class="ml-3 min-w-0 flex-1 text-sm/6">
                                                    <label :for="`courier_` + item.rate_id" class="font-medium text-gray-900">{{item.name}}</label>
                                                </div>
                                                <div class="flex">
                                                    <span class="font-medium text-gray-900"><span v-if="item.currency_prefix" v-html="item.currency_prefix"></span>{{item.formated_price}}<span v-if="item.currency_suffix" v-html="item.currency_suffix"></span></span>
                                                </div>
                                            </div>

                                        </template>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <button
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                            type="submit"
                            class="mt-6 w-full rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >{{ i18n.global.t('continue') }}</button>

                        <p class="mt-6 flex justify-center text-sm font-medium text-gray-500">
                            {{ i18n.global.t('continue_description') }}
                        </p>
                    </form>
                </div>
            </section>
        </div>

        <Map
            :selected-point="selectedPoint"
            :selected-point-index="selectedPointIndex"
            :selected-point-service="selectedPoint?.service"
            :show="showMap"
            @close="showMap = false"
            @selected="mapSelectedPoint"
        ></Map>
    </CheckoutLayout>
</template>

<script setup>

import { onMounted, ref, watch, computed } from 'vue'
import { createI18n } from 'vue-i18n';

import { Head, useForm, usePage } from "@inertiajs/vue3";

import CheckoutLayout from "@/Layouts/CheckoutLayout.vue"
import Cart from "@/Pages/Checkout/Cart.vue"
import Map from "./Map.vue"

const props = defineProps({
    cart: {
        type: Object
    },
    settings: {
        type: Object
    }
});

const messages = {
    en: {
        head_title: 'Shipping',
        pickup_points: 'Pickup points',
        delivery_to_address: 'Delivery to address',
        no_pickup_points_available: 'No pickup points available',
        continue: 'Continue',
        continue_description: 'Payment method will be available in the next step',
        from_address: 'from the address',
        map: 'map',
        at_your_place: 'At your place in 1-2 days',
        change_pickup_point: 'Change pickup point'
    },
    pl: {
        head_title: 'Dostawa',
        pickup_points: ' Odbiór w punkcie',
        delivery_to_address: 'Dostawa na adres',
        no_pickup_points_available: 'Brak dostępnych punktów odbioru',
        continue: 'Dalej',
        continue_description: 'Metoda płatności będzie dostępna w następnym kroku',
        from_address: 'od adresu',
        map: 'mapa',
        at_your_place: 'Dostawa w ciągu 1-2 dni',
        change_pickup_point: 'Zmień punkt'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const furgonetkaDeliveryToType = computed(() => {
    const data = {};

    for (const shipment of props.cart.shipping_rates) {
        for (const rate of shipment.shipping_rates) {
            const service = Object.entries(props.settings.furgonetka_deliveryToType)
                .find(([key]) => key === rate.rate_id);

            if (service) {
                const [serviceKey, serviceValue] = service;
                data[serviceKey] = serviceValue;
            }
        }
    }

    return data;
});

const form = useForm({
    package_id: null,
    rate_id: null,
    furgonetka: null,
    redirect_back: false
});

const loading = ref(
    Object.fromEntries(Object.values(furgonetkaDeliveryToType.value).map(service => [service, true]))
);

const isLoading = computed(() => Object.values(loading.value).some(status => status));

const showMap = ref(false);
const selectedPoint = ref(null);
const selectedPointIndex = ref(0);

const points = ref([]);

const address = ref({
    postcode: props.cart.shipping_address.postcode,
    street: props.cart.shipping_address.address_1,
    city: props.cart.shipping_address.city,
    country_code: props.cart.shipping_address.country
});

const coordinates = ref(null);

const selectedPickupPointCode = ref({
    package_id: null,
    rate_id: null,
    furgonetka: null
});

const furgonetka = ref({
    selected_point: {
        service: props.cart.extensions.furgonetka.selected_point.service,
        service_type: props.cart.extensions.furgonetka.selected_point.service_type,
        code: props.cart.extensions.furgonetka.selected_point.code,
        name: props.cart.extensions.furgonetka.selected_point.name,
    }
});

onMounted(() => {
    // Set default selected shipping
    for (const shipment of props.cart.shipping_rates) {
        const foundMethod = shipment.shipping_rates.find((rate) => rate.selected === true);
        if (foundMethod) {
            selectedPickupPointCode.value = {
                package_id: shipment.package_id,
                rate_id: foundMethod.rate_id
            }
        }
    }

    // Init load pickup points throw furgonetka API
    Object.values(furgonetkaDeliveryToType.value).forEach(service => load(service));
});

watch(
    () => selectedPickupPointCode.value,
    () => {
        const point = points.value
            .find((point) => point.shipping_rate.rate_id === selectedPickupPointCode.value.rate_id);

        furgonetka.value.selected_point = {
            service: null,
            service_type: null,
            code: null,
            name: null
        }

        if (point) {
            furgonetka.value.selected_point = {
                service: point.service,
                service_type: point.service_type,
                code: point.code,
                name: point.name
            }
        }

        submit(true);
    },
);

const submit = (redirectBack = false) => {
    form.package_id = selectedPickupPointCode.value.package_id;
    form.rate_id = selectedPickupPointCode.value.rate_id;
    form.furgonetka = null;
    form.redirect_back = false;

    if (furgonetka.value.selected_point.code) {
        form.furgonetka = {
            selected_point: {
                service: furgonetka.value.selected_point.service,
                service_type: furgonetka.value.selected_point.service_type,
                code: furgonetka.value.selected_point.code,
                name: furgonetka.value.selected_point.name,
            }
        }
    }

    const options = {
        showProgress: true
    };

    if (redirectBack) {
        form.redirect_back = true;
    }

    form.post(route('cart.select-shipping-rate'), options);
};

const mapSelectedPoint = (value) => {
    value.selectedPoint.distance = calculateDistance(
        coordinates.value.latitude,
        coordinates.value.longitude,
        value.selectedPoint.coordinates.latitude,
        value.selectedPoint.coordinates.longitude
    );

    value.selectedPoint.shipping_rate = pickupPointShippingRate(value.selectedPoint);

    selectedPickupPointCode.value = {
        package_id: value.selectedPoint.shipping_rate.package_id,
        rate_id: value.selectedPoint.shipping_rate.rate_id
    }

    furgonetka.value.selected_point.service = value.selectedPoint.service;
    furgonetka.value.selected_point.service_type = value.selectedPoint.service_type;
    furgonetka.value.selected_point.code = value.selectedPoint.code;
    furgonetka.value.selected_point.name = value.selectedPoint.name;

    points.value[value.selectedPointIndex] = value.selectedPoint;
    showMap.value = false;
}

const pickupPointShippingRate = (point) => {
    const rateId = Object.keys(furgonetkaDeliveryToType.value)
        .find((key) => furgonetkaDeliveryToType.value[key] === point.service);

    if (!rateId) {
        return null;
    }

    for (const shipment of props.cart.shipping_rates) {
        const foundMethod = shipment.shipping_rates.find((rate) => rate.rate_id === rateId);
        if (foundMethod) {
            foundMethod.package_id = shipment.package_id;
            return foundMethod;
        }
    }

    return null;
}

const load = (service) => {
    let code = '';

    if (furgonetka.value.selected_point.service === service) {
        code = furgonetka.value.selected_point.code + ' ';
    }

    axios.post(
        'https://api.furgonetka.pl/points/map',
        {
            location: {
                // address: address.value,
                search_phrase: code + ' ' + address.value.postcode + ' ' + address.value.street + ' ' + address.value.city + ' ' + address.value.country_code
            },
            filters: {
                services: [service],
                point_types: ['delivery_points'],
                limit: 5,
            }
        }, {
            headers: {
                'X-Requested-With': null
            }
        }
    ).then(response => {
        if (!response.data.points.length) {
            loading.value[service] = false;
            return;
        }

        if (!coordinates.value) {
            coordinates.value = {
                latitude: response.data.coordinates.latitude,
                longitude: response.data.coordinates.longitude
            };
        }

        const point = response.data.points[0];
        point.distance = calculateDistance(
            coordinates.value.latitude,
            coordinates.value.longitude,
            point.coordinates.latitude,
            point.coordinates.longitude
        );
        point.shipping_rate = pickupPointShippingRate(point);

        points.value.push(point);
        loading.value[service] = false;

        if (point.shipping_rate.selected) {
            furgonetka.value.selected_point.service = point.service;
            furgonetka.value.selected_point.service_type = point.service_type;
            furgonetka.value.selected_point.code = point.code;
            furgonetka.value.selected_point.name = point.name;
        }

        points.value = points.value.sort((a, b) => {
            return a.shipping_rate.price - b.shipping_rate.price;
        });
    });
}

const calculateDistance = (lat1, lng1, lat2, lng2) => {
    const R = 6371;
    const toRadians = angle => (angle * Math.PI) / 180;

    const phi1 = toRadians(lat1);
    const phi2 = toRadians(lat2);
    const deltaPhi = toRadians(lat2 - lat1);
    const deltaLambda = toRadians(lng2 - lng1);

    const a =
        Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
        Math.cos(phi1) * Math.cos(phi2) *
        Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
}

</script>
