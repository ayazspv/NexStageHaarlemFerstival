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
