
export function urlFriendly(title: string) {
    return title
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '-');
}

export function route(name: string, path: string) {
    return `${urlFriendly(name)}/${path}`
}
