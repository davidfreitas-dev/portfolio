import api from '@/api';
import type { ApiResponse, Project, ProjectListResponse } from '@/types';

export const projectService = {
  async fetchProjects(page = 1, limit = 10, search = '', status?: string | number): Promise<ProjectListResponse> {
    const params: Record<string, any> = { page, limit, search };
    if (status !== undefined && status !== 'all') {
      params.is_active = status;
    }
    const response = await api.get<ApiResponse<ProjectListResponse>>('/admin/projects', {
      params
    }) as unknown as ApiResponse<ProjectListResponse>;
    if (!response.data) throw new Error('No projects data received');
    return response.data;
  },

  async fetchProjectById(id: number): Promise<Project> {
    const response = await api.get<ApiResponse<Project>>(`/admin/projects/${id}`) as unknown as ApiResponse<Project>;
    if (!response.data) throw new Error('No project data received');
    return response.data;
  },

  async saveProject(payload: FormData): Promise<Project> {
    const response = await api.post<ApiResponse<Project>>('/admin/projects', payload, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }) as unknown as ApiResponse<Project>;
    return response.data as Project;
  },

  async deleteProject(id: number): Promise<void> {
    await api.delete<ApiResponse<void>>(`/admin/projects/${id}`);
  }
};
