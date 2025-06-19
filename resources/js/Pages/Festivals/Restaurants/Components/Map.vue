<script lang="ts" setup>

import { defineProps, onMounted, ref, watch } from 'vue';
import L from 'leaflet';
import axios from 'axios';

const props = defineProps({
    restaurant: Object
});

const error = ref<boolean>(false)

// Leaflet map and marker refs
let map: L.Map | null = null
let marker: L.Marker | null = null

// Load map centered at given coordinates
const loadMap = (lat: number, lon: number): void => {
  if (map) {
    map.setView([lat, lon], 15)
    if (marker) {
      marker.setLatLng([lat, lon])
    } else {
      marker = L.marker([lat, lon]).addTo(map)
    }
  } else {
    map = L.map('address-map').setView([lat, lon], 15)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19
    }).addTo(map)

    marker = L.marker([lat, lon]).addTo(map)
  }
}

// Convert address to coordinates via OpenStreetMap Nominatim API
const geocodeAddress = async (address: string): Promise<void> => {
  try {
    error.value = false

    const response = await axios.get('https://nominatim.openstreetmap.org/search', {
      params: {
        q: address,
        format: 'json',
        addressdetails: 1,
        limit: 1
      },
      headers: {
        'Accept-Language': 'en',
        'User-Agent': 'YourAppName (your@email.com)' // Replace with real user agent
      }
    })

    const result = response.data[0]
    if (result) {
      const lat = parseFloat(result.lat)
      const lon = parseFloat(result.lon)
      loadMap(lat, lon)
    } else {
      error.value = true
    }
  } catch (err) {
    console.error('Geocoding failed:', err)
    error.value = true
  }
}

// Watch for prop changes
watch(
  () => props.restaurant?.location,
  (newAddress) => {
    if (newAddress) geocodeAddress(newAddress)
  },
  { immediate: true }
)

onMounted(() => {
  if (props.restaurant?.location) geocodeAddress(props.restaurant.location)
})

</script>
<template>
    <div class="map-section d-flex p-5 flex-column align-items-center gap align-self-stretch">
        <h2>Where are we located?</h2>
        <p>Here below is a simple map of our location</p>
        <div class="map-content d-flex flex-column align-items-start gap-2 w-75 mt-5">
            <div class="map mb-3">
                <div id="address-map" style="height: 600px;" class="border rounded"></div>
                <p v-if="error" class="text-danger mt-2">Could not find location for this address.</p>
            </div>
            <div class="location d-flex align-items-center gap-3">
                <i class="fa fa-location"></i>
                <p>{{ restaurant.location }}</p>
            </div>
            <div class="phone d-flex align-items-center gap-3">
                <i class="fa fa-phone"></i>
                <p>{{ restaurant.contact_number }}</p>
            </div>
        </div>
    </div>
</template>
<style scoped>
p {
    margin: 0;
}

.map-section {
    background: #FAFAFA;
}

.map-section>h2 {
    color: #F8A219;
    text-align: center;
    text-shadow: 0px 0px 35px rgba(248, 162, 25, 0.35);
    font-family: Bayon;
    font-size: 70px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.map-section>p {
    color: #000;
    font-family: NotoSansTamil;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.map {
    width: 100%;
    height: 600px;
    background-color: #000;
    border-radius: 15px;
}

.map-content p {
    color: #000;
    font-family: Bayon;
    font-size: 24px;
    font-style: normal;
    font-weight: 400;
    line-height: normal;
}

.map-content i {
    color: #000;
    font-size: 24px;
}
</style>