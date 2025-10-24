export interface NodeData {
  data: any
  index: string
  title?: string
  days?: number
  start_date?: string | null
  end_date?: string | null
  restriction_end_date: string  | null
  status_id?: number | null
  substatus_id: number
  priority_id?: number | null
  sprint_id?: number | null
  user_id?: number | null
  percentage?: number
  percentage_planned?: number
  percentage_progress?:number
  depends_me: string | null
  i_depend: string | null
  comments: string | null
  slack: number | null
}

export interface TreeNode {
  id: number | string
  parent?: TreeNode
  children?: TreeNode[]
}
