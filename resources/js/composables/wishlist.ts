import { ref } from 'vue';

export const wishlist = ref<any[]>([]);

export function fetchWishlistItems() {
    const storedWishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    wishlist.value = storedWishlist;
    wishlist.value = [...wishlist.value]; // Trigger reactivity update
}

export function addToWishlist(festivalId: number, festivalName: string) {
    if (!wishlist.value.some(item => item.festival_id === festivalId)) {
        wishlist.value.push({ festival_id: festivalId, name: festivalName });
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


