import { ref } from 'vue';

// Simple localStorage-based cart (no database)
export const cart = ref([]);

// Load cart from localStorage
export const fetchCartItems = () => {
    try {
        const stored = localStorage.getItem('cart');
        if (stored) {
            cart.value = JSON.parse(stored);
        } else {
            cart.value = [];
        }
        console.log('Cart loaded from localStorage:', cart.value);
    } catch (error) {
        console.error('Error loading cart:', error);
        cart.value = [];
    }
};

// Save cart to localStorage
const saveCart = () => {
    try {
        localStorage.setItem('cart', JSON.stringify(cart.value));
    } catch (error) {
        console.error('Error saving cart:', error);
    }
};

// Add item to cart
export const addToCart = async (festivalId: number, festivalName: string, festivalCost: number, quantity: number = 1, ticketType: string = 'standard') => {
    try {
        if (ticketType === 'standard' && festivalId > 0) {
            // Fetch price from API for standard festival tickets
            const response = await fetch(`/api/festivals/${festivalId}/price`);
            
            if (response.ok) {
                const data = await response.json();
                console.log('Price data from API:', data); // Debugging
                
                // Use nullish coalescing instead of logical OR
                festivalCost = data.price ?? 25.00; 
            } else {
                console.error('Error fetching price:', response.status);
                festivalCost = 25.00; // Default price if API fails
            }
        }

        // Check if item already exists
        const existingItem = cart.value.find(item => item.festival_id === festivalId);

        if (existingItem) {
            // Update quantity (max 10 per festival)
            const newQuantity = existingItem.quantity + quantity;
            if (newQuantity > 10) {
                alert('Maximum 10 tickets allowed per festival');
                return;
            }
            existingItem.quantity = newQuantity;
        } else {
            // Add new item
            cart.value.push({
                festival_id: festivalId,
                festival_name: festivalName,
                festival_cost: festivalCost,
                quantity: quantity
            });
        }

        saveCart();
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
};

// Update cart item quantity
export const updateCartItem = (festivalId: number, newQuantity: number) => {
    try {
        if (newQuantity < 1) {
            removeCartItem(festivalId);
            return;
        }

        if (newQuantity > 10) {
            alert('Maximum 10 tickets allowed per festival');
            return;
        }

        const item = cart.value.find(item => item.festival_id === festivalId);
        if (item) {
            item.quantity = newQuantity;
            saveCart();
        }
    } catch (error) {
        console.error('Error updating cart item:', error);
        alert('Failed to update item');
    }
};

// Remove item from cart
export const removeCartItem = (festivalId: number) => {
    try {
        cart.value = cart.value.filter(item => item.festival_id !== festivalId);
        saveCart();
    } catch (error) {
        console.error('Error removing cart item:', error);
        alert('Failed to remove item');
    }
};

// Clear entire cart
export const clearCart = () => {
    try {
        cart.value = [];
        localStorage.removeItem('cart');
        console.log('Cart cleared successfully');
    } catch (error) {
        console.error('Error clearing cart:', error);
        alert('Failed to clear cart');
    }
};

// Prepare checkout data for payment
export const prepareCheckoutData = () => {
    const totalAmount = cart.value.reduce((total, item) => total + (item.festival_cost * item.quantity), 0);

    const items = cart.value.map(item => ({
        festival_id: item.festival_id,
        festivalName: item.festival_name,
        quantity: item.quantity,
        festivalCost: item.festival_cost,
        // Legacy format for compatibility
        festivalID: item.festival_id,
        festivalQuantity: item.quantity,
    }));

    return {
        totalAmount,
        items,
    };
};

// Get cart totals
export const getCartTotals = () => {
    const totalQuantity = cart.value.reduce((total, item) => total + item.quantity, 0);
    const totalCost = cart.value.reduce((total, item) => total + (item.festival_cost * item.quantity), 0);

    return {
        totalQuantity,
        totalCost,
        itemCount: cart.value.length,
    };
};
