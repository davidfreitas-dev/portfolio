import type { PaginatedData } from '../shared';

export interface Project {
  id: number;
  title: string;
  description: string;
  slug: string;
  summary: string;
  link: string;
  github_link: string;
  image: string;
  is_active?: number;
  technologies: string[];
}

export interface ProjectListResponse extends PaginatedData {
  projects: Project[];
}
