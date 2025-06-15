import { ref } from 'vue';
import { useForm, Link, router, usePage } from '@inertiajs/vue3';

export const cart = ref<any[]>([]);

export function fetchCartItems() {
    const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.value = storedCart;
    cart.value = [...cart.value]; // Trigger reactivity update
}

export async function addToCart(
    festivalId: number, 
    festivalName: string, 
    quantity: number = 1, 
    ticketType: string = 'standard', 
    details: any = {}
) {
    let festivalCost = 0;
    
    try {
        if (ticketType === 'standard' && festivalId > 0) {
            // Always fetch price from API
            const response = await fetch(`/api/festivals/${festivalId}/price`);
            if (response.ok) {
                const data = await response.json();
                festivalCost = data.price ?? 0;
            }
        } else {
            // Get special ticket prices from API
            const response = await fetch('/api/special-tickets/prices');
            if (response.ok) {
                const data = await response.json();
                if (ticketType === 'day_pass') {
                    festivalCost = data.day_pass ?? 0;
                } else if (ticketType === 'full_pass') {
                    festivalCost = data.full_pass ?? 0;
                }
            }
        }
    } catch (error) {
        console.error('Error fetching price:', error);
    }

    // Generate a unique ID for special tickets
    const itemId = festivalId > 0 ? festivalId : `${ticketType}_${JSON.stringify(details)}`;
    
    const existingItem = cart.value.find(item => {
        if (ticketType === 'standard') {
            return item.festival_id === festivalId;
        } else {
            return item.ticket_type === ticketType && 
                   JSON.stringify(item.details) === JSON.stringify(details);
        }
    });

    // Clear any potential duplicate event bindings
    if (existingItem) {
        existingItem.quantity = existingItem.quantity + 1;
    } else {
        cart.value.push({
            festival_id: festivalId,
            festival_name: festivalName,
            festival_cost: festivalCost,
            quantity: 1,
            ticket_type: ticketType,
            details: details
        });
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
