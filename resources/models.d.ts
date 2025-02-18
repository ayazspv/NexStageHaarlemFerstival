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
    content: string[] | null,
    date?: string,
    link?: string,
}
