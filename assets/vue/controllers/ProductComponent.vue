<template>
    <a :href="path" :class="isHome ? '' : 'bg-white p-4 rounded-lg shadow-md hover:shadow-xl transition duration-300'">
        <img :src="image" alt="No image available" class="w-full h-40 object-cover rounded-md">
        <hr class="my-4">
        <h3 class="text-gray-800 font-bold mt-4">{{ name }}</h3>
        <div class="flex justify-between items-center mt-2">
            <p class="text-gray-600">{{ price }} €</p>
            <div v-if="isConnected" class="flex items-center gap-2 z-10">
                <form v-if="wishlist" :action="pathRemoveWishlist" method="POST">
                    <input type="hidden" name="_token" :value="removeWishlistToken">
                    <button type="submit">
                    <svg class="h-6 w-6 cursor-pointer opacity-75 hover:opacity-100 transition duration-300" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ff0000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4.79501 5C4.62688 5.13993 4.46746 5.29304 4.31803 5.45838C3.90017 5.92074 3.56869 6.46964 3.34255 7.07374C3.1164 7.67785 3 8.32532 3 8.9792C3 9.63308 3.1164 10.2806 3.34255 10.8847C3.56869 11.4888 3.90017 12.0377 4.31803 12.5L10.5162 19.3582C11.3103 20.2368 12.6897 20.2368 13.4838 19.3582L16.1691 16.3869" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M3 3L19 19" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M9.22217 4.37901C9.76813 4.62924 10.2642 4.99601 10.6821 5.45837V5.45837C11.3874 6.23883 12.6127 6.23883 13.3181 5.45837V5.45837C14.162 4.52459 15.3066 4 16.5001 4C17.6935 4 18.8381 4.52459 19.682 5.45837C20.526 6.39215 21.0001 7.65863 21.0001 8.97919C21.0001 10.2998 20.526 11.5662 19.682 12.5L18.7783 13.5" stroke="#ff0000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>  
                    </button>
                </form>
                <a v-else :href="pathAddWishlist">
                    <svg class="h-6 w-6 cursor-pointer opacity-75 hover:opacity-100 transition duration-300" version="1.0" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 64 64" enable-background="new 0 0 64 64" xml:space="preserve" fill="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path fill="#ff4124" d="M58.714,29.977c0,0-0.612,0.75-1.823,1.961S33.414,55.414,33.414,55.414C33.023,55.805,32.512,56,32,56 s-1.023-0.195-1.414-0.586c0,0-22.266-22.266-23.477-23.477s-1.823-1.961-1.823-1.961C3.245,27.545,2,24.424,2,21 C2,13.268,8.268,7,16,7c3.866,0,7.366,1.566,9.899,4.101l0.009-0.009l4.678,4.677c0.781,0.781,2.047,0.781,2.828,0l4.678-4.677 l0.009,0.009C40.634,8.566,44.134,7,48,7c7.732,0,14,6.268,14,14C62,24.424,60.755,27.545,58.714,29.977z"></path> <path fill="#ff4124" d="M58.714,29.977c0,0-0.612,0.75-1.823,1.961S33.414,55.414,33.414,55.414C33.023,55.805,32.512,56,32,56 s-1.023-0.195-1.414-0.586c0,0-22.266-22.266-23.477-23.477s-1.823-1.961-1.823-1.961C3.245,27.545,2,24.424,2,21 C2,13.268,8.268,7,16,7c3.866,0,7.366,1.566,9.899,4.101l0.009-0.009l4.678,4.677c0.781,0.781,2.047,0.781,2.828,0l4.678-4.677 l0.009,0.009C40.634,8.566,44.134,7,48,7c7.732,0,14,6.268,14,14C62,24.424,60.755,27.545,58.714,29.977z"></path> <g> <path fill="#000000" d="M48,5c-4.418,0-8.418,1.791-11.313,4.687l-3.979,3.961c-0.391,0.391-1.023,0.391-1.414,0 c0,0-3.971-3.97-3.979-3.961C24.418,6.791,20.418,5,16,5C7.163,5,0,12.163,0,21c0,3.338,1.024,6.436,2.773,9 c0,0,0.734,1.164,1.602,2.031s24.797,24.797,24.797,24.797C29.953,57.609,30.977,58,32,58s2.047-0.391,2.828-1.172 c0,0,23.93-23.93,24.797-24.797S61.227,30,61.227,30C62.976,27.436,64,24.338,64,21C64,12.163,56.837,5,48,5z M58.714,29.977 c0,0-0.612,0.75-1.823,1.961S33.414,55.414,33.414,55.414C33.023,55.805,32.512,56,32,56s-1.023-0.195-1.414-0.586 c0,0-22.266-22.266-23.477-23.477s-1.823-1.961-1.823-1.961C3.245,27.545,2,24.424,2,21C2,13.268,8.268,7,16,7 c3.866,0,7.366,1.566,9.899,4.101l0.009-0.009l4.678,4.677c0.781,0.781,2.047,0.781,2.828,0l4.678-4.677l0.009,0.009 C40.634,8.566,44.134,7,48,7c7.732,0,14,6.268,14,14C62,24.424,60.755,27.545,58.714,29.977z"></path> <path fill="#000000" d="M48,11c-0.553,0-1,0.447-1,1s0.447,1,1,1c4.418,0,8,3.582,8,8c0,0.553,0.447,1,1,1s1-0.447,1-1 C58,15.478,53.522,11,48,11z"></path> </g> </g> </g></svg>
                </a>
                <a :href="pathAddCart">
                    <svg class="h-6 w-6 cursor-pointer opacity-75 hover:opacity-100 transition duration-300" fill="#000000" height="200px" width="200px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 490.996 490.996" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g id="Icons_1_"> <g> <path d="M484.058,112.28c-7.247-10.404-19.144-16.614-31.816-16.614h-94.624c13.291,2.775,24.603,11.714,29.943,24.615 c1.063,2.569,1.761,5.212,2.283,7.869h62.396c2.063,0,3.997,1.015,5.155,2.67c1.175,1.698,1.444,3.862,0.73,5.791 l-44.992,121.107c-0.905,2.451-3.267,4.102-5.887,4.102H154.939L114.734,90.314c-5.01-21.286-23.772-36.153-45.631-36.153H24.361 C10.912,54.161,0,65.065,0,78.522s10.912,24.362,24.361,24.362h43.286l54.131,230.919c4.914,20.864,23.058,35.479,44.36,36.042 c-12.532,9.103-20.764,23.765-20.764,40.436c0,27.662,22.429,50.078,50.09,50.078c27.662,0,50.072-22.416,50.072-50.078 c0-16.605-8.17-31.212-20.623-40.326h93.421c-12.454,9.114-20.634,23.721-20.634,40.326c0,27.662,22.428,50.078,50.083,50.078 c27.646,0,50.072-22.416,50.072-50.078c0-16.605-8.187-31.212-20.634-40.326h22.714c13.448,0,24.361-10.901,24.361-24.361 c0-13.457-10.913-24.361-24.361-24.361h-231.07l-6.313-26.931h244.693c16.113,0,30.703-10.143,36.338-25.256l44.994-121.118 C492.986,136.046,491.305,122.732,484.058,112.28z"></path> <path d="M275.701,209.63c1.776,1.785,4.109,2.673,6.437,2.673c2.334,0,4.667-0.888,6.426-2.673l67.007-66.987 c2.621-2.609,3.396-6.525,1.986-9.935c-0.923-2.221-3.986-5.64-8.422-5.64c-6.472,0-25.886,0-25.886,0V95.665v-55.89 c-0.017-5.035-4.094-9.137-9.138-9.137h-63.964c-5.044,0-9.12,4.102-9.12,9.12v55.908v31.412c0,0-19.408,0-25.878,0 c-4.144,0-7.473,3.332-8.424,5.622c-1.41,3.41-0.635,7.334,1.962,9.943L275.701,209.63z"></path> </g> </g> </g> </g></svg>
                </a>
            </div>
        </div>
    </a>
</template>

<script setup>
defineProps({
    name: String,
    price: Number,
    image: String,
    wishlist: Boolean,
    path: String,
    isHome: Boolean,
    isConnected: Boolean,
    pathRemoveWishlist: String,
    removeWishlistToken: String,
    pathAddWishlist: String,
    pathAddCart: String,
});
</script>
