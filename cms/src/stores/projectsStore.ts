import { defineStore } from 'pinia';
import { ref, type Ref } from 'vue';
import { useLoading } from '@/composables/useLoading';
import axios from '@/api/axios';

export interface Project {
  id?: number;
  title: string;
  description: string;
  link?: string;
  image?: File | string; // string = URL já existente, File = upload novo
  is_active?: number;
  technologies: number[]; // lista de IDs de tecnologias relacionadas
}

export const useProjectsStore = defineStore('projects', () => {
  const { isLoading, withLoading } = useLoading();
  const projects: Ref<Project[]> = ref([]);
  const selectedProject: Ref<Project | null> = ref(null);
  const totalItems: Ref<number> = ref(0);
  const totalPages: Ref<number> = ref(1);

  const fetchProjects = async (page = 1, limit = 10, search = '') => {
    await withLoading(async () => {
      const response = await axios.get('/projects', {
        params: { page, limit, search }
      });
      projects.value = response.data.projects;
      totalItems.value = response.data.total;
      totalPages.value = response.data.pages;
    });
  };

  const fetchProjectById = async (id: number) => {
    await withLoading(async () => {
      const response = await axios.get(`/projects/${id}`);
      selectedProject.value = response.data;
    });
  };

  const saveProject = async (payload: Project): Promise<void> => {
    await withLoading(async () => {
      const formData = new FormData();

      if (payload.id) formData.append('id', String(payload.id));
      formData.append('title', payload.title);
      formData.append('description', payload.description);
      if (payload.link) formData.append('link', payload.link);
      if (payload.image instanceof File) formData.append('image', payload.image);
      if (payload.is_active !== undefined) formData.append('is_active', String(payload.is_active));
      formData.append('technologies', payload.technologies.join(','));

      await axios.post('/projects', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    });
  };

  const deleteProject = async (id: number): Promise<void> => {
    await withLoading(async () => {
      await axios.delete(`/projects/${id}`);
    });
  };

  return {
    projects,
    selectedProject,
    totalItems,
    totalPages,
    isLoading,
    fetchProjects,
    fetchProjectById,
    saveProject,
    deleteProject
  };
});
