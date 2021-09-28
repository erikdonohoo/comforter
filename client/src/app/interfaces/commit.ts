export interface ComforterCommit {
  app_id: number;
  branch_name: string;
  coverage: string;
  id: number;
  sha: string;
  total_lines: number;
  total_lines_covered: number;
  coverage_path: string;
  base_commit?: ComforterCommit;
  created_at: Date;
  updated_at: Date;
}
