import { ComforterCommit } from './commit';

export interface ComforterApp {
  id: number;
  name: string;
  gitlab_project_id: number;
  primary_branch_name: string;
  coverage: string;
  commits?: ComforterCommit[];
  latest_commit: ComforterCommit;
  created_at: Date;
  updated_at: Date;
  deleted_at: Date;
}
