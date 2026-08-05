import { computed, ref, watch } from 'vue';

export function useStorefrontCart(slug) {
    const key = `storefront_cart:${slug}`;
    const cart = ref(JSON.parse(localStorage.getItem(key) || '[]'));
    watch(cart, value => localStorage.setItem(key, JSON.stringify(value)), { deep: true });
    const count = computed(() => cart.value.reduce((sum, item) => sum + Number(item.quantity), 0));
    const total = computed(() => cart.value.reduce((sum, item) => sum + Number(item.price) * Number(item.quantity), 0));
    const add = (product, quantity = 1) => {
        const item = cart.value.find(row => row.product_id === product.id);
        if (item) item.quantity = Math.min(item.stock, item.quantity + quantity);
        else cart.value.push({ product_id: product.id, name: product.name, image: product.image,
            price: Number(product.selling_price), quantity: Math.min(Number(product.available_stock), Number(quantity)), stock: Number(product.available_stock) });
    };
    const remove = id => { cart.value = cart.value.filter(item => item.product_id !== id); };
    const clear = () => { cart.value = []; };
    return { cart, count, total, add, remove, clear };
}
