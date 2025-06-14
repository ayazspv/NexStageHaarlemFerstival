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
    const itemId = festivalId > 0 ? festivalId : `${ticketType}_${JSON.stringify(details)}`;
    
    const existingItem = wishlist.value.find(item => {
        if (ticketType === 'standard') {
            return item.festival_id === festivalId;
        } else {
            return item.ticket_type === ticketType && 
                   JSON.stringify(item.details) === JSON.stringify(details);
        }
    });

    if (!existingItem) {
        wishlist.value.push({ 
            festival_id: festivalId, 
            name: festivalName,
            ticket_type: ticketType,
            details: details 
        });
        localStorage.setItem('wishlist', JSON.stringify(wishlist.value));
        wishlist.value = [...wishlist.value]; // Trigger reactivity update
    }
}

export function removeFromWishlist(festivalId: number) {
    wishlist.value = wishlist.value.filter(item => item.festival_id !== festivalId);
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


