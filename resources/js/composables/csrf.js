import { usePage } from '@inertiajs/vue3';

export function useCsrf() {
    const page = usePage();
    const csrfToken = page.props.csrf_token || '';
    
    return {
        csrfToken
    };
}