import api from '@/api';
import type { ApiResponse, Experience, ExperienceListResponse } from '@/types';

export type CreateExperiencePayload = Omit<Experience, 'id'>;
export type UpdateExperiencePayload = Partial<CreateExperiencePayload>;

export const experienceService = {
  async fetchExperiences(page = 1, limit = 10, search = ''): Promise<ExperienceListResponse> {
    const response = await api.get<ApiResponse<ExperienceListResponse>>('/admin/experiences', {
      params: { page, limit, search }
    }) as unknown as ApiResponse<ExperienceListResponse>;
    if (!response.data) throw new Error('No experiences data received');
    return response.data;
  },

  async fetchExperienceById(id: number): Promise<Experience> {
    const response = await api.get<ApiResponse<Experience>>(`/admin/experiences/${id}`) as unknown as ApiResponse<Experience>;
    if (!response.data) throw new Error('No experience data received');
    return response.data;
  },

  async createExperience(payload: CreateExperiencePayload): Promise<Experience> {
    const response = await api.post<ApiResponse<Experience>>('/admin/experiences', payload) as unknown as ApiResponse<Experience>;
    return response.data as Experience;
  },

  async updateExperience(id: number, payload: UpdateExperiencePayload): Promise<void> {
    await api.put<ApiResponse<void>>(`/admin/experiences/${id}`, payload);
  },

  async deleteExperience(id: number): Promise<void> {
    await api.delete<ApiResponse<void>>(`/admin/experiences/${id}`);
  }
};
