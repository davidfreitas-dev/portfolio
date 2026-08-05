import type { PaginatedData } from '../shared';

export interface Experience {
  id: number;
  title: string;
  description: string;
  start_date: string;
  end_date: string | null;
  sort_order: number;
}

export interface ExperienceListResponse extends PaginatedData {
  experiences: Experience[];
}
