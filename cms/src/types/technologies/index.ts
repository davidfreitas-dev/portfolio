import type { PaginatedData } from '../shared';

export interface Technology {
  id: number;
  name: string;
  slug?: string;
  image: string;
  sort_order?: number;
}

export interface TechnologyListResponse extends PaginatedData {
  technologies: Technology[];
}
