<template>
    <TransitionRoot as="template" :show="open">
        <Dialog :initial-focus="modalFocus" class="relative z-10" @close="close">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 w-screen">
                <div class="flex min-h-full items-end justify-center items-center p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-0 scale-95" enter-to="opacity-100 translate-y-0 scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 scale-100" leave-to="opacity-0 translate-y-0 scale-95">
                        <DialogPanel :class="{'pb-24' : isIOS && isSafari && isMobile, 'pb-[calc(7rem+0.7rem)]' : isIOS && isChrome && isMobile}" class="p-4 relative transform overflow-hidden sm:rounded-lg bg-white text-left shadow-xl transition-all w-full max-w-5xl m-0 h-[calc(100vh)] sm:m-8 sm:h-[calc(100vh-4rem)]">
                            <div class="relative flex flex-col w-full h-full">
                                <button type="button" class="focus:outline-none absolute -right-4 -top-4 rounded-md p-2 text-gray-400" @click="close">
                                    <XMarkIcon class="size-6" aria-hidden="true" />
                                </button>
                                <h3 class="text-base font-semibold text-gray-900">{{ i18n.global.t('pickup_points') }}</h3>

                                <div class="mt-6">
                                    <div class="relative">
                                        <label
                                            v-if="search || focused"
                                            for="search"
                                            class="absolute -top-2 left-2 inline-block rounded-lg bg-white px-1 text-xs font-medium text-gray-900"
                                        >{{ i18n.global.t('find_address') }}</label>
                                        <input
                                            autocomplete="off"
                                            type="text"
                                            name="search"
                                            id="search"
                                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"
                                            :placeholder="focused ? '' : i18n.global.t('find_address')"
                                            v-model="search"
                                            @input="handleInput"
                                            @focus="focused = true"
                                            @blur="focused = false"
                                        />
                                        <ul v-if="suggestions.length" class="absolute z-10 w-full bg-white shadow-lg">
                                            <li
                                                v-for="(suggestion, index) in suggestions"
                                                :key="index"
                                                class="px-4 py-2 text-sm text-gray-700 cursor-pointer hover:bg-indigo-100"
                                                @click="handleSuggestionSelected(suggestion)"
                                            >
                                                {{ suggestion.description }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mt-4 flex-1 relative">
                                    <div ref="mapContainer" class="w-full h-full"></div>
                                    <div ref="smDetected" class="hidden sm:block"></div>
                                    <div ref="markerInfoContainer" v-if="selectedPoint" class="absolute w-full left-0 bottom-0 sm:shadow-md sm:w-auto sm:top-0 sm:bottom-auto">
                                        <div class="relative bg-white w-full p-4 sm:ml-2 sm:mt-2 sm:w-80">
                                            <button type="button" class="focus:outline-none absolute right-0 top-0 rounded-md p-2 text-gray-400" @click="() => {selectedPoint = null; renderMarkers();}">
                                                <XMarkIcon class="size-6" aria-hidden="true" />
                                            </button>
                                            <div class="font-semibold text-gray-900">
                                                {{ selectedPoint.name }}
                                            </div>
                                            <div class="text-sm/5 text-gray-700 mt-2">
                                                <p v-if="selectedPoint.address.street">{{selectedPoint.address.street}}</p>
                                                <p v-if="selectedPoint.address.postcode">{{selectedPoint.address.postcode}} {{selectedPoint.address.city}}</p>
                                                <p v-if="selectedPoint.description">{{selectedPoint.description}}</p>
                                            </div>

                                            <div
                                                class="mt-4"
                                            >
                                                <button @click="selected" type="submit" class="w-full rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">{{ i18n.global.t('select') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="hidden" ref="placesServiceContainer"></div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>

import { ref, watch, onMounted, nextTick } from "vue";
import { createI18n } from 'vue-i18n';

import { usePage } from "@inertiajs/vue3";
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";

import { Loader } from "@googlemaps/js-api-loader";
import { MarkerClusterer } from "@googlemaps/markerclusterer";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    selectedPoint: {
        type: Object
    },
    selectedPointIndex: {
        type: Number
    },
    selectedPointService: {
        type: String
    }
});

const messages = {
    en: {
        pickup_points: 'Pickup points',
        find_address: 'Find address',
        select: 'Select'
    },
    pl: {
        pickup_points: ' Punkty odbioru',
        find_address: 'Wpisz adres',
        select: 'Wybierz'
    }
};
const i18n = createI18n({
    locale: usePage().props.locale,
    messages
});

const emit = defineEmits(['close', 'selected']);

const close = () => {
    emit('close');
};

const selected = () => {
    emit('selected', {
        selectedPoint: selectedPoint.value,
        selectedPointIndex: props.selectedPointIndex
    });
};

const mapZoom = 17;
const mapMaxZoom = 19;

const modalFocus = ref(null);

const open = ref(false)

const mapContainer = ref(null);
const markerInfoContainer = ref(null);
const placesServiceContainer = ref(null);

let mapInstance = null;
let marker = null;

const focused = ref(false);

const search = ref('');
const suggestions = ref([]);
const sessionToken = ref(null);

const smDetected = ref(null);
const isMobile = ref(false);

const updateWidth = async () => {
    await nextTick();
    isMobile.value = smDetected.value.offsetWidth === 0;
};

let mapCenterIsSet = false;
let points = [];
const selectedPoint = ref(null);
const selectedPointService = ref(null);

const apiKey = import.meta.env.VITE_GOOGLEMAPS_API_KEY;
const options = ref({
    zoom: mapZoom,
    maxZoom: mapMaxZoom,
    fullscreenControl: false,
    clickableIcons: false,
    mapTypeControl: false,
    mapId: import.meta.env.VITE_GOOGLEMAPS_MAP_ID,
    gestureHandling: "greedy"
});

const isIOS = ref(false);
const isSafari = ref(false);
const isChrome = ref(false);
const isFirefox = ref(false);
const isOther = ref(false);

onMounted(() => {
    const ua = navigator.userAgent.toLowerCase();

    isIOS.value = /iphone|ipad|ipod/.test(ua);

    if (/safari/.test(ua) && !/crios/.test(ua) && !/fxios/.test(ua)) {
        isSafari.value = true;
    } else if (/crios/.test(ua)) {
        isChrome.value = true;
    } else if (/fxios/.test(ua)) {
        isFirefox.value = true;
    } else {
        isOther.value = true;
    }
});

watch(
    () => props.show,
    () => {
        if (props.show) {
            open.value = true;

            selectedPoint.value = props.selectedPoint;
            selectedPointService.value = props.selectedPointService;

            options.value.center = {
                lat: selectedPoint.value.coordinates.latitude,
                lng: selectedPoint.value.coordinates.longitude
            };

            modalFocus.value = null;

            mapContainer.value = null;
            markerInfoContainer.value = null;

            mapInstance = null;
            marker = null;

            focused.value = false;

            search.value = '';
            suggestions.value = [];
            sessionToken.value = null;

            mapCenterIsSet = false;
            points = [];

            window.addEventListener("resize", updateWidth);
            updateWidth();

            initializeMap();
        }

        if (!props.show) {
            window.removeEventListener("resize", updateWidth);
            open.value = false;
        }
    },
);

const setMapCenter = (panTo = true) => {
    if (mapCenterIsSet) {
        return;
    }

    mapCenterIsSet = true;

    const bounds = mapInstance.getBounds();
    const mapWidth = mapContainer.value.offsetWidth;
    const mapHeight = mapContainer.value.offsetHeight;

    if (!markerInfoContainer.value) {
        return;
    }

    const markerCoords = selectedPoint.value.coordinates;
    const offsetWidth = markerInfoContainer.value.offsetWidth / 2;
    const offsetHeight = markerInfoContainer.value.offsetHeight / 2;

    const lngPerPixel = (bounds.getNorthEast().lng() - bounds.getSouthWest().lng()) / mapWidth;
    const latPerPixel = (bounds.getNorthEast().lat() - bounds.getSouthWest().lat()) / mapHeight;
    let lngOffset = lngPerPixel * offsetWidth;
    let latOffset = latPerPixel * offsetHeight;

    let center = {
        lat: markerCoords.latitude,
        lng: markerCoords.longitude - lngOffset
    };

    if (isMobile.value) {
        center = {
            lat: markerCoords.latitude - latOffset,
            lng: markerCoords.longitude
        };
    }

    if (panTo) {
        mapInstance.panTo(center);
        return;
    }

    mapInstance.setCenter(center);
};

const initializeLoader = () => {
    return new Loader({
        apiKey,
        version: 'weekly',
    });
};

const initializeMap = async () => {
    const loader = initializeLoader();
    const { Map } = await loader.importLibrary('maps');
    mapInstance = new Map(mapContainer.value, options.value);

    mapInstance.addListener('idle', async () => {
        setMapCenter(false);
        const center = mapInstance.getCenter();
        await loadPoints(center.lat(), center.lng());
        await renderMarkers();
    });
};

const renderMarkers = async () => {
    const { AdvancedMarkerElement } = await initializeLoader().importLibrary("marker");
    const clusterMarkers = [];

    for (const item of points) {
        const content = document.createElement('img');
        content.src = selectedPoint.value?.coordinates.latitude === item.coordinates.latitude &&
        selectedPoint.value?.coordinates.longitude === item.coordinates.longitude
            ? '/storage/markers/' + item.service + '_active.svg'
            : '/storage/markers/' + item.service + '_inactive.svg';

        const marker = new AdvancedMarkerElement({
            position: { lat: item.coordinates.latitude, lng: item.coordinates.longitude },
            content: content
        });

        marker.addListener('click', () => {
            let value = item;
            mapCenterIsSet = false;

            if (
                selectedPoint.value?.coordinates.latitude === item.coordinates.latitude &&
                selectedPoint.value?.coordinates.longitude === item.coordinates.longitude
            ) {
                value = null;
            }

            selectedPoint.value = value;
            renderMarkers();
        });

        clusterMarkers.push(marker);
    }

    if (mapInstance.markerCluster) {
        mapInstance.markerCluster.clearMarkers();
    }

    mapInstance.markerCluster = new MarkerClusterer({
        markers: clusterMarkers,
        map: mapInstance,
        renderer: {
            render: ({ count, position }) => {
                const clusterElement = document.createElement("div");
                clusterElement.style.cssText = `
                    background-color: rgb(79 70 229 / var(--tw-bg-opacity, 1));
                    color: white;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    font-size: 14px;
                `;
                clusterElement.textContent = count;

                return new AdvancedMarkerElement({
                    position,
                    content: clusterElement,
                });
            },
        }
    });

    setMapCenter();
};

const loadPoints = async (latitude, longitude) => {
    const mapBounds = getMapBounds();

    await axios.post(
        'https://api.furgonetka.pl/points/map',
        {
            location: {
                coordinates: {
                    latitude: latitude,
                    longitude: longitude
                }
            },
            filters: {
                services: [selectedPointService.value],
                limit: 2000,
                map_bounds: mapBounds
            }
        }, {
            headers: {
                'X-Requested-With': null
            }
        }
    ).then(response => {
        points = response.data.points;
    });
}

const getMapBounds = () => {
    const bounds = mapInstance.getBounds();

    const northEast = bounds.getNorthEast();
    const southWest = bounds.getSouthWest();

    return {
        north_west: {
            latitude: northEast.lat(),
            longitude: southWest.lng(),
        },
        north_east: {
            latitude: northEast.lat(),
            longitude: northEast.lng(),
        },
        south_west: {
            latitude: southWest.lat(),
            longitude: southWest.lng(),
        },
        south_east: {
            latitude: southWest.lat(),
            longitude: northEast.lng(),
        },
    };
};

const getPlacesApiClient = async () => {
    const loader = initializeLoader();
    return await loader.importLibrary('places');
};

const loadSuggestions = async (input) => {
    if (!input || input.trim().length < 3) {
        suggestions.value = [];
        return;
    }
    const places = await getPlacesApiClient();
    if (!sessionToken.value) {
        sessionToken.value = new places.AutocompleteSessionToken();
    }
    const autocompleteService = new places.AutocompleteService();
    autocompleteService.getPlacePredictions(
        { input, sessionToken: sessionToken.value },
        (results, status) => {
            if (status === "OK") {
                suggestions.value = results;
            } else {
                suggestions.value = [];
            }
        }
    );
};

const debounceTimeout = ref(null);

const handleInput = (event) => {
    const input = event.target.value;
    search.value = input;

    if (debounceTimeout.value) {
        clearTimeout(debounceTimeout.value);
    }

    debounceTimeout.value = setTimeout(() => {
        loadSuggestions(input);
    }, 300);
};

const handleSuggestionSelected = async (suggestion) => {
    search.value = suggestion.description;
    suggestions.value = [];

    const places = await getPlacesApiClient();
    const placesService = new places.PlacesService(placesServiceContainer.value);

    placesService.getDetails(
        { placeId: suggestion.place_id, sessionToken: sessionToken.value },
        async (place, status) => {
            const position = {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng(),
            };

            await setMarker(position);
        }
    );

    sessionToken.value = null;
};

const setMarker = async (position) => {
    mapInstance.setCenter(position);
    mapInstance.setZoom(mapZoom);

    if (marker) {
        marker.position = position;
        return;
    }

    const { AdvancedMarkerElement } = await initializeLoader().importLibrary("marker");
    marker = new AdvancedMarkerElement({
        position: position,
        map: mapInstance
    });
}

</script>
