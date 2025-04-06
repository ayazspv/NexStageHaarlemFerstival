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

export type CMS = {
    id: number;
    festival_id: number;
    parent_id: number | null;
    title: string;
    content: string | null; // single string value from the CMS 'content' field
    link: string;
    image_path: string | null;
    order: number; // single numeric value
};

export type Festival = {
    id: number;
    name: string;
    description?: string | null;
    image_path: string;
    date?: string;
    link?: string;
    isGame?: boolean;
    ticket_amount: number; 
    created_at?: string;
    updated_at?: string;
}

export type Game = {
    id?: number;
    question: string;
    option1: string;
    option2: string;
    option3: string;
    option4: string;
    correct_option: number | null;
    thumbnail?: File | null | string;
    stamp?: File | null | string;
}

export interface JazzFestival {
    id?: number;
    festival_id: number;
    band_name: string;
    performance_datetime: string; // ISO date string
    ticket_price: number;
    band_description: string;
    band_details: string;
    band_image?: string;
    second_image?: string;
    created_at?: string;
    updated_at?: string;
}


