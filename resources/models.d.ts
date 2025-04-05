export enum Role {
    Admin,
    User
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
    description: string | null;
    image: string; // using the accessor from Laravel (getImageAttribute)
    date?: string;
    link?: string;
    cmsPages?: CMS[]; // include related CMS pages
};

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
