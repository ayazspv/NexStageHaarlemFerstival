import { ref, computed } from 'vue';

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

// Clear cart
export const clearCart = () => {
    cart.value = [];
    saveCart();
    console.log('Cart cleared');
};

// Add item to cart
export const addToCart = async (festivalId: number, festivalName: string, quantity: number = 1, ticketType: string = 'standard', details: any = {}) => {
    try {
        let festivalCost = 0;

        if (ticketType === 'day_pass') {
            // Fixed price for day pass
            festivalCost = 35.00;
        } else if (ticketType === 'full_pass') {
            // Fixed price for full festival pass
            festivalCost = 80.00;
        } else if (ticketType === 'standard' && festivalId > 0) {
            // Fetch price from API for standard festival tickets
            try {
                const response = await fetch(`/api/festivals/${festivalId}/price`);
                
                if (response.ok) {
                    const data = await response.json();
                    console.log('Price data from API:', data);
                    
                    // Use nullish coalescing instead of logical OR
                    festivalCost = data.price ?? 25.00;
                } else {
                    console.error('Error fetching price:', response.status);
                    festivalCost = 25.00; // Default price if API fails
                }
            } catch (error) {
                console.error('Error fetching price:', error);
                festivalCost = 25.00; // Default price if fetch throws
            }
        } else if (festivalId <= 0) {
            // For special tickets with negative IDs
            if (festivalId === -1) {
                festivalCost = 35.00; // Day pass
            } else if (festivalId === -2) {
                festivalCost = 80.00; // Full pass
            }
        }

        // Check if item already exists
        const existingItem = cart.value.find(item => {
            if (ticketType === 'standard') {
                return item.festival_id === festivalId;
            } else {
                // For special tickets, check festival_id and ticket_type
                return item.festival_id === festivalId && item.ticket_type === ticketType;
            }
        });

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
                quantity: quantity,
                ticket_type: ticketType,
                details: details
            });
        }

        saveCart();
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
};

// Add jazz event to cart
export const addJazzEventToCart = async (
    festivalId: number,
    eventId: number, 
    artistName: string,
    performanceDay: number,
    performanceTime: string,
    quantity: number = 1
) => {
    try {
        // Always fetch the current price from the API for consistency
        let ticketPrice = 25.00; // Default fallback price
        
        try {
            const response = await fetch(`/api/jazz-events/${eventId}/price`);
            if (response.ok) {
                const data = await response.json();
                ticketPrice = data.price ?? 25.00;
                console.log(`Fetched price for artist ${artistName}: €${ticketPrice}`);
            }
        } catch (error) {
            console.error('Error fetching price:', error);
        }
        
        // Check if this jazz event is already in cart
        const existingItem = cart.value.find(item => 
            item.event_id === eventId && item.ticket_type === 'jazz_event'
        );
        
        if (existingItem) {
            // Update quantity (max 10)
            const newQuantity = existingItem.quantity + quantity;
            existingItem.quantity = Math.min(newQuantity, 10);
        } else {
            // Add new item with standardized structure
            cart.value.push({
                festival_id: festivalId,
                festival_name: artistName, // Use artist name instead of 'Jazz Festival'
                event_id: eventId,
                ticket_type: 'jazz_event',
                artist_name: artistName,
                performance_day: performanceDay,
                performance_time: performanceTime,
                festival_cost: ticketPrice, // Use the fetched price
                quantity: quantity
            });
        }
        
        saveCart();
    } catch (error) {
        console.error('Error adding jazz event to cart:', error);
    }
};

// Consolidate jazz events
export function consolidateJazzEvents() {
    const seenArtists = {};
    const newCart = [];
    
    cart.value.forEach(item => {
        if (item.ticket_type === 'jazz_event') {
            // Clean artist name for comparison
            const artistKey = (item.artist_name || item.name || "")
                .replace(/^Jazz\s*-\s*/i, '')
                .trim()
                .toLowerCase();
                
            if (seenArtists[artistKey]) {
                // Add quantity to existing item
                seenArtists[artistKey].quantity += item.quantity;
                // Cap at 10
                seenArtists[artistKey].quantity = Math.min(seenArtists[artistKey].quantity, 10);
                
                // Very important: use the same price for consistency
                seenArtists[artistKey].festival_cost = Math.max(
                    seenArtists[artistKey].festival_cost,
                    item.festival_cost || 0
                );
            } else {
                // First time seeing this artist
                seenArtists[artistKey] = {...item};
                newCart.push(item);
            }
        } else {
            // Non-jazz events pass through unchanged
            newCart.push(item);
        }
    });
    
    cart.value = newCart;
    saveCart();
}

// Prepare data for checkout
export function prepareCheckoutData() {
    // Make sure cart is loaded
    if (cart.value.length === 0) {
        fetchCartItems();
    }
    
    // Calculate total cost
    const totalAmount = cart.value.reduce(
        (total, item) => total + (item.festival_cost * item.quantity), 
        0
    );
    
    // Format items for checkout
    const items = cart.value.map(item => ({
        festival_id: item.festival_id,
        festivalID: item.festival_id,
        festivalName: item.artist_name || item.festival_name || 'Festival Ticket',
        festivalQuantity: item.quantity,
        quantity: item.quantity,
        festivalCost: item.festival_cost,
        event_id: item.event_id || null,
        ticket_type: item.ticket_type || 'standard',
        artist_name: item.artist_name || null,
        performance_day: item.performance_day || null,
        performance_time: item.performance_time || null,
    }));

    return {
        totalAmount,
        items,
    };
}

// Update cart item quantity
export const updateCartItem = (festivalId: number, newQuantity: number, eventId?: number | null, ticketType?: string) => {
    const itemIndex = cart.value.findIndex(item => {
        if (eventId && item.ticket_type === 'jazz_event') {
            return item.event_id === eventId;
        } else if (ticketType && (ticketType === 'day_pass' || ticketType === 'full_pass')) {
            return item.festival_id === festivalId && item.ticket_type === ticketType;
        }
        return item.festival_id === festivalId && !item.event_id;
    });
    
    if (itemIndex !== -1) {
        if (newQuantity <= 0) {
            // Remove the item
            cart.value.splice(itemIndex, 1);
        } else if (newQuantity > 10) {
            // Limit to maximum 10 tickets
            cart.value[itemIndex].quantity = 10;
        } else {
            // Update quantity
            cart.value[itemIndex].quantity = newQuantity;
        }
        saveCart();
    }
};