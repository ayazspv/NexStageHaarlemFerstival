import NightAtTeylers from "./js/Pages/Festivals/NightAtTeylers.vue";

export enum Role {
    Admin,
    User
}

export enum FestivalType {
    Jazz,
    Yummy,
    History,
    NightAtTeylers,
    Dance,
}

export type User = {
    id: number,
    firstName: string,
    lastName: string,
    phoneNumber: string,
    email: string,
    Role: role
}

export type Festival = {
    id: number;
    name: string;
    description?: string | null;
    image_path: string;
    date?: string;
    time_slot?: string;
    link?: string;
    isGame?: boolean;
    ticket_amount: number;
    created_at?: string;
    updated_at?: string;
}

export type Game = {
    id?: number;
    title: string,
    question: string;
    option1: string;
    option2: string;
    option3: string;
    option4: string;
    correct_option: number | null;
    hint: string;
    thumbnail?: File | null | string;
    stamp?: File | null | string;
}

export interface JazzFestival {
    id?: number;
    festival_id: number;
    band_name: string;
    performance_datetime: string;
    performance_day: number; // 24, 25, 26, or 27
    ticket_price: number;
    band_description: string;
    band_details: string;
    band_image?: string;
    second_image?: string;
    created_at?: string;
    updated_at?: string;
}

export type Order = {
    id: number,
    user_id: number,
    user: User,
    total_price: number,
    status: string,
    ordered_at: string,
    tickets: Ticket[],
}

export type Ticket = {
    id: number,
    order_id: number,
    festival_id: number,
    festival: Festival,
    detail_ticket_id: number,
    detail_ticket: JazzFestival, // | Restaurant | History | Dance !! Add this once you have added your models for festivals
    qr_code: string,
}
