export interface SubsetInterface<T> {
    items: T[],
    count?: number,
    totalCount?: number,
    hasPreviousPage?: boolean
    hasNextPage?: boolean
}