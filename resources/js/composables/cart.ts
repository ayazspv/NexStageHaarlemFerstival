import { ref } from 'vue';

export const cart = ref<any[]>([]);

export function fetchCartItems() {
    const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.value = storedCart;
    cart.value = [...cart.value]; // Trigger reactivity update
}

export function addToCartBackup(festivalId: number) {
    const existingItem = cart.value.find(item => item.festival_id === festivalId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.value.push({ festival_id: festivalId, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart.value));
    cart.value = [...cart.value]; // Trigger reactivity update
}

export function addToCart(festivalId: number, festivalName: string, festivalCost: number) {
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

export function useCart() {
    return {
        cart,
        fetchCartItems,
        addToCart,
        updateCartItem
    };
}

// export function layerOneOutput(totalAmount: number, items: Array) {
//     const storedData = JSON.parse(totalAmount, items)
// }

