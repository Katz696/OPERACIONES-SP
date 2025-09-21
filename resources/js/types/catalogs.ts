export interface Customer {
    id: number | null
    name:string | null
    email:string |null
    phone: string | null
    address: string | null
    created_at: string | null
    updated_at: string | null
}
export interface User {
    id: number | null,
    name: string | null,
    email: string | null,
    email_verified_at: string | null,
    created_at: string | null,
    updated_at: string | null
}

export interface Status {
    id: number,
    status: null | string,
    color: null | string,
    created_at: null | string,
    updated_at: null | string,
}

export interface Project {
    id: number | null,
    customer_id: number | null,
    user_id: number | null,
    index: number,
    title: string,
    percentage: number,
    days: number,
    start_date: null | string,
    end_date: null | string,
    real_end_date: string | null,
    restriction_start_date: null | string,
    restriction_end_date: null | string,
    created_at: null | string,
    updated_at: null | string,
    status: Status
}

export interface Response<T> {
    status: string,
    data: null | T[]
}

