import { ref } from 'vue';
import { useForm, Link, router, usePage } from '@inertiajs/vue3';

export const cart = ref<any[]>([]);

export function fetchCartItems() {
    const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.value = storedCart;
    cart.value = [...cart.value]; // Trigger reactivity update
}

export async function addToCart(festivalId: number, festivalName: string, festivalCost: number) {
    const existingItem = cart.value.find(item => item.festival_id === festivalId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.value.push({ festival_id: festivalId, festival_name: festivalName, festival_cost: festivalCost, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart.value));
    cart.value = [...cart.value]; // Trigger reactivity update
}

export function updateCartItem(cartItemId: number, quantity: number) {
    const item = cart.value.find(item => item.festival_id === cartItemId);
    if (item) {
        item.quantity = quantity;
        if (item.quantity <= 0) {
            cart.value = cart.value.filter(item => item.festival_id !== cartItemId);
        }
        localStorage.setItem('cart', JSON.stringify(cart.value));
        cart.value = [...cart.value]; // Trigger reactivity update
    }
}

export function clearCart() {
    cart.value = []; // Clear the cart
    localStorage.setItem('cart', JSON.stringify(cart.value)); // Update localStorage
}

export function addAllToCart(items: { festival_id: number; name: string; quantity: number }[]) {
    items.forEach(item => {
        const existingItem = cart.value.find(cartItem => cartItem.festival_id === item.festival_id);
        if (existingItem) {
            existingItem.quantity += item.quantity; // Update quantity if the item already exists
        } else {
            cart.value.push({ festival_id: item.festival_id, name: item.name, quantity: item.quantity });
        }
    });
    localStorage.setItem('cart', JSON.stringify(cart.value)); // Update localStorage
    cart.value = [...cart.value]; // Trigger reactivity update
}

// New function to prepare data for checkout
export function prepareCheckoutData() {
    const items = cart.value.map(item => ({
        festivalID: item.festival_id,
        festivalName: item.festival_name,
        festivalQuantity: item.quantity,
        festivalCost: item.festival_cost,
    }));

    const totalAmount = cart.value.reduce((total, item) => total + (item.festival_cost * item.quantity), 0);

    return {
        totalAmount,
        items,
    };
}

export function useCart() {
    return {
        cart,
        fetchCartItems,
        addToCart,
        addAllToCart,
        updateCartItem,
        clearCart,
        prepareCheckoutData,
    };
}
