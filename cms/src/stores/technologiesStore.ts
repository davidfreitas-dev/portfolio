import { defineStore } from 'pinia';
import { ref, type Ref } from 'vue';
import { technologyService } from '@/services/technologyService';
import type { Technology } from '@/types';

export interface TechnologyPayload {
  id?: number;
  name: string;
  image?: File | string;
}

export const useTechnologiesStore = defineStore('technologies', () => {
  const technologies: Ref<Technology[]> = ref([]);
  const selectedTechnology: Ref<Technology | null> = ref(null);
  const totalItems: Ref<number> = ref(0);
  const totalPages: Ref<number> = ref(1);

  const fetchTechnologies = async (page = 1, limit = 10, search = '') => {
    const data = await technologyService.fetchTechnologies(page, limit, search);
    technologies.value = data.technologies || [];
    totalItems.value = data.total ?? 0;
    totalPages.value = data.pages ?? 1;
  };

  const fetchTechnologyById = async (id: number) => {
    const data = await technologyService.fetchTechnologyById(id);
    selectedTechnology.value = data;
  };

  const saveTechnology = async (payload: TechnologyPayload): Promise<void> => {
    const formData = new FormData();
    
    if (payload.id) formData.append('id', String(payload.id));      
    formData.append('name', payload.name);      
    if (payload.image instanceof File) formData.append('image', payload.image);

    await technologyService.saveTechnology(formData);
  };

  const deleteTechnology = async (id: number): Promise<void> => {
    await technologyService.deleteTechnology(id);
  };

  return {
    technologies,
    selectedTechnology,
    totalItems,
    totalPages,
    fetchTechnologies,
    fetchTechnologyById,
    saveTechnology,
    deleteTechnology
  };
});
