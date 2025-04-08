import { ref } from 'vue';
import {useForm, Link, router, usePage} from '@inertiajs/vue3';

export const cart = ref<any[]>([]);

export function fetchCartItems() {
    const storedCart = JSON.parse(localStorage.getItem('cart') || '[]');
    cart.value = storedCart;
    cart.value = [...cart.value]; // Trigger reactivity update
}

/* export async function addToCart(festivalId: number, festivalName: string) {
    
    const page = usePage();
    const csrfToken = page.props.csrf_token as string || ""; 

    const existingItem = cart.value.find(item => item.festival_id === festivalId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
           cart.value.push({ festival_id: festivalId, name: festivalName, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart.value));
    cart.value = [...cart.value]; // Trigger reactivity update

     
    try {
        const response = await fetch('/api/send-mail', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                to: 'aron.lakatos123@gmail.com',
                subject: 'New Item Added to Cart',
                body: `An item with ID ${festivalId} and name ${festivalName} has been added to your cart.`,
                altBody: `An item with ID ${festivalId} and name ${festivalName} has been added to your cart.`,
            }),
        });

        if (!response.ok) {
            throw new Error(`Failed to send email: ${response.statusText} (${response.body})`);
        }

        const result = await response.json();
        console.log('Email sent successfully:', result.message);
    } catch (error) {
        console.error('Error sending email:', error);
    }
    
} */

export function addToCart(festivalId: number, festivalName: string, festivalCost: number) {
    const existingItem = cart.value.find(item => item.festival_id === festivalId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.value.push({ festival_id: festivalId, festival_name: festivalName, festival_cost: festivalCost, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart.value));
    cart.value = [...cart.value]; // Trigger reactivity update

    /* 
    try {
        const response = await fetch('/api/send-mail', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                to: 'aron.lakatos123@gmail.com',
                subject: 'New Item Added to Cart',
                body: `An item with ID ${festivalId} and name ${festivalName} has been added to your cart.`,
                altBody: `An item with ID ${festivalId} and name ${festivalName} has been added to your cart.`,
            }),
        });

        if (!response.ok) {
            throw new Error(`Failed to send email: ${response.statusText} (${response.body})`);
        }

        const result = await response.json();
        console.log('Email sent successfully:', result.message);
    } catch (error) {
        console.error('Error sending email:', error);
    }
    */
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

export function useCart() {
    return {
        cart,
        fetchCartItems,
        addToCart,
        addAllToCart, // Add the new function here
        updateCartItem,
        clearCart,
        
    };
}

// export function layerOneOutput(totalAmount: number, items: Array) {
//     const storedData = JSON.parse(totalAmount, items)
// }

