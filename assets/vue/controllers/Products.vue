<template>
    <div class="flex justify-between items-center my-6">
        <h2 class="text-2xl font-bold text-gray-800">Nos Articles</h2>
        <select v-model="sortOrder" @change="sortProducts" class="p-2 border rounded-md">
            <option value="default">Pertinence</option>
            <option value="priceAsc">Prix croissant</option>
            <option value="priceDesc">Prix décroissant</option>
        </select>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <ProductComponent 
            v-for="product in sortedProducts" 
            :key="product.id" 
            :name="product.name" 
            :price="product.price" 
            image="" 
            :wishlist="false"
            :path="`${props.productPath.replace('ID_PLACEHOLDER',`${product.id}`)}`"
            :isHome="false"
        />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ProductComponent from './ProductComponent.vue';

const props = defineProps({
    products: Array,
    productPath: String
});

console.log(props.productPath);

const products = JSON.parse(props.products);
const sortOrder = ref("default");

const sortedProducts = computed(() => {
    let sorted = [...products];
    
    if (sortOrder.value === "priceAsc") {
        return sorted.sort((a, b) => a.price - b.price);
    } else if (sortOrder.value === "priceDesc") {
        return sorted.sort((a, b) => b.price - a.price);
    }
    return sorted;
});
</script>
