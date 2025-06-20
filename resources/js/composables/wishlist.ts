import { ref } from 'vue';

export const wishlist = ref<any[]>([]);

export function fetchWishlistItems() {
    const storedWishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    wishlist.value = storedWishlist;
    wishlist.value = [...wishlist.value]; // Trigger reactivity update
}

export function addToWishlist(
    festivalId: number, 
    festivalName: string, 
    ticketType: string = 'standard', 
    details: any = {}
) {
    // Set the correct price based on ticket type
    let price = 0;
    if (ticketType === 'day_pass') {
        price = 35.00;
    } else if (ticketType === 'full_pass') {
        price = 80.00;
    }
    
    // Prevent adding duplicates
    const existingItem = wishlist.value.find(item => {
        if (ticketType === 'standard') {
            return item.festival_id === festivalId;
        } else if (ticketType === 'jazz_event' && item.event_id) {
            return item.festival_id === festivalId && item.event_id === details.event_id;
        } else {
            return item.festival_id === festivalId && 
                   item.ticket_type === ticketType && 
                   JSON.stringify(item.details) === JSON.stringify(details);
        }
    });

    if (!existingItem) {
        wishlist.value.push({ 
            festival_id: festivalId, 
            name: festivalName,
            ticket_type: ticketType,
            details: details,
            price: price
        });
        localStorage.setItem('wishlist', JSON.stringify(wishlist.value));
        wishlist.value = [...wishlist.value]; // Trigger reactivity update
    }
}

// Standardized addJazzEventToWishlist function
export function addJazzEventToWishlist(
    festivalId: number,
    eventId: number, 
    artistName: string,
    performanceDay: number,
    performanceTime: string
) {
    // Check if this event is already in the wishlist
    const isAlreadyInWishlist = wishlist.value.some(
        item => item.event_id === eventId && item.ticket_type === 'jazz_event'
    );

    if (!isAlreadyInWishlist) {
        wishlist.value.push({
            festival_id: festivalId,
            event_id: eventId,
            ticket_type: 'jazz_event',
            artist_name: artistName,
            performance_day: performanceDay,
            performance_time: performanceTime
        });
        
        localStorage.setItem('wishlist', JSON.stringify(wishlist.value));
    }
}

export function removeFromWishlist(festivalId: number, eventId?: number) {
    if (eventId) {
        // For jazz events or other events with specific IDs, remove by both festival and event ID
        wishlist.value = wishlist.value.filter(item => 
            !(item.festival_id === festivalId && item.event_id === eventId)
        );
    } else {
        // For regular festival tickets, remove by festival ID only
        wishlist.value = wishlist.value.filter(item => 
            !(item.festival_id === festivalId && !item.event_id)
        );
    }
    
    localStorage.setItem('wishlist', JSON.stringify(wishlist.value));
    wishlist.value = [...wishlist.value]; // Trigger reactivity update
}

export function clearWishlist() {
    wishlist.value = []; // Clear the wishlist
    localStorage.removeItem('wishlist'); // Remove wishlist from localStorage
    wishlist.value = [...wishlist.value]; // Trigger reactivity update
}

export function useWishlist() {
    return {
        wishlist,
        fetchWishlistItems,
        addToWishlist,
        removeFromWishlist,
        clearWishlist 
    };
}

