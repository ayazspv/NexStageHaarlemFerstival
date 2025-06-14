import { ref } from 'vue';
import {useForm, Link, router, usePage} from '@inertiajs/vue3';

export const cart = ref<any[]>([]);

export function fetchCartItems() {
    const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.value = storedCart;
    cart.value = [...cart.value];
}


export async function addToCart(
    festivalId: number, 
    festivalName: string, 
    festivalCost: number, 
    quantity: number = 1, 
    ticketType: string = 'standard', 
    details: any = {}
) {
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

    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.value.push({ 
            festival_id: festivalId, 
            festival_name: festivalName, 
            festival_cost: festivalCost, 
            quantity: quantity,
            ticket_type: ticketType,
            details: details
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart.value));
    cart.value = [...cart.value];

    const page = usePage();
    const csrfToken = page.props.csrf_token as string || "";
}

export function updateCartItem(cartItemId: number, quantity: number) {
    const item = cart.value.find(item => item.festival_id === cartItemId);
    if (item) {
        item.quantity = quantity;
        if (item.quantity <= 0) {
            cart.value = cart.value.filter(item => item.festival_id !== cartItemId);
        }
        localStorage.setItem('cart', JSON.stringify(cart.value));
        cart.value = [...cart.value];
    }
}

export function clearCart() {
    cart.value = [];
    localStorage.setItem('cart', JSON.stringify(cart.value));
}

export function addAllToCart(items: { festival_id: number; name: string; quantity: number }[]) {
    items.forEach(item => {
        const existingItem = cart.value.find(cartItem => cartItem.festival_id === item.festival_id);
        if (existingItem) {
            existingItem.quantity += item.quantity;
        } else {
            cart.value.push({ festival_id: item.festival_id, name: item.name, quantity: item.quantity });
        }
    });
    localStorage.setItem('cart', JSON.stringify(cart.value));
    cart.value = [...cart.value];
}

export function useCart() {
    return {
        cart,
        fetchCartItems,
        addToCart,
        addAllToCart,
        updateCartItem,
        clearCart,
    };
}
