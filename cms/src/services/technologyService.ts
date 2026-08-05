import api from '@/api';
import type { ApiResponse, Technology, TechnologyListResponse } from '@/types';

export const technologyService = {
  async fetchTechnologies(page = 1, limit = 10, search = ''): Promise<TechnologyListResponse> {
    const response = await api.get<ApiResponse<TechnologyListResponse>>('/admin/technologies', {
      params: { page, limit, search }
    }) as unknown as ApiResponse<TechnologyListResponse>;
    if (!response.data) throw new Error('No technologies data received');
    return response.data;
  },

  async fetchTechnologyById(id: number): Promise<Technology> {
    const response = await api.get<ApiResponse<Technology>>(`/admin/technologies/${id}`) as unknown as ApiResponse<Technology>;
    if (!response.data) throw new Error('No technology data received');
    return response.data;
  },

  async saveTechnology(payload: FormData): Promise<Technology> {
    const response = await api.post<ApiResponse<Technology>>('/admin/technologies', payload, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }) as unknown as ApiResponse<Technology>;
    return response.data as Technology;
  },

  async deleteTechnology(id: number): Promise<void> {
    await api.delete<ApiResponse<void>>(`/admin/technologies/${id}`);
  }
};
