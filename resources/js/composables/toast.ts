import { ref, reactive } from 'vue';

interface Toast {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info' | 'warning';
    timeout: number;
}

const toasts = reactive<Toast[]>([]);
let nextId = 0;

export function useToast() {
    const showToast = (
        message: string, 
        type: 'success' | 'error' | 'info' | 'warning' = 'info', 
        timeout: number = 3000
    ) => {
        const id = nextId++;
        const toast = { id, message, type, timeout };
        toasts.push(toast);
        
        setTimeout(() => {
            const index = toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                toasts.splice(index, 1);
            }
        }, timeout);
    };

    return {
        toasts,
        showToast
    };
}