import type { PaginatedData } from '../shared';
import type { Technology } from '../technologies';

export interface Project {
  id: number;
  title: string;
  description: string;
  slug: string;
  summary: string;
  link: string;
  github_link: string;
  image: string;
  technologies: Technology[];
}

export interface ProjectListResponse extends PaginatedData {
  projects: Project[];
}
