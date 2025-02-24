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

export type Festival = {
    id: number,
    name: string,
    description: string | null,
    image: string,
    date?: string,
    link?: string,
}

export type CMS = {
    id: number,
    festival_id: number,
    parent_id: number | null,
    title: string,
    content: string[] | null,
    link: string,
    image_path: string | null,
    order: number[],
}
