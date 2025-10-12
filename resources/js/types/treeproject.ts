// resources/js/types/Project.ts

export interface Activity {
    data: {
        id: number
        delivery_id: number
        status_id: number
        substatus_id: number
        priority_id: number
        user_id: number
        index: string
        title: string
        percentage: number
        percentage_planned: number
        percentage_progress: number
        days: number
        start_date: string
        end_date: string
        real_end_date: string
        restriction_start_date: string
        restriction_end_date: string
        activity_id: number | null
        depends_me: string[] | null
        i_depend: string[] | null
        comments: string | null
    }
}

export interface Delivery {
    data: {
        id: number
        project_id: number
        phase_id: number
        status_id: number
        substatus: number
        priority_id: number
        user_id: number
        sprint_id: number
        index: string
        title: string
        percentage: number
        percentage_planned: number
        percentage_progress: number
        days: number
        start_date: string
        end_date: string
        real_end_date: string
        restriction_start_date: string
        restriction_end_date: string
        depends_me: string[] | null
        i_depend: string[] | null
        comments: string | null
    }
    activities: Activity[]
}

export interface Phase {
    data: {
        id: number
        project_id: number
        status_id: number
        substatus: number
        title: string
        percentage: number
        percentage_planned: number
        percentage_progress: number
        index: string
        days: number
        start_date: string
        end_date: string
        real_end_date: string
        restriction_start_date: string
        restriction_end_date: string
        depends_me: string[] | null
        i_depend: string[] | null
        comments: string | null
    }
    deliveries: Delivery[]
}

export interface ProjectData {
    data: {
        id: number
        customer_id: number
        user_id: number
        index: string
        title: string
        percentage: number
        percentage_planned: number
        percentage_progress: number
        status_id: number
        days: number
        start_date: string
        end_date: string
        real_end_date: string
        restriction_start_date: string
        restriction_end_date: string
        comments: string | null
    }
    phases: Phase[]
}

export interface Customer {
    id: number
    title: string
    email: string
    phone: string
    address: string
}

export interface Status {
    id: number
    status: string
    color: string
}
export interface SubStatus {
    id: number
    substatus: string
}
export interface Priority {
    id: number
    priority: string
    color: string
}

export interface Sprint {
    id: number
    sprint: string
}

export interface User {
    id: number
    name: string
}

export interface FullProjectResponse {
    project: ProjectData
    customer: Customer
    statuses: Status[]
    substatuses: SubStatus[]
    priorities: Priority[]
    users: User[]
    sprints: Sprint[]
}
