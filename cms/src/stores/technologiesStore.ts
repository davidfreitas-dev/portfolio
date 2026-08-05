import { defineStore } from 'pinia';
import { ref, type Ref } from 'vue';
import { useLoading } from '@/composables/useLoading';
import axios from '@/api';

export interface Technology {
  id?: number;
  name: string;
  image?: File | string; // string = URL já existente, File = upload novo
}

export const useTechnologiesStore = defineStore('technologies', () => {
  const { isLoading, withLoading } = useLoading();
  const technologies: Ref<Technology[]> = ref([]);
  const selectedTechnology: Ref<Technology | null> = ref(null);
  const totalItems: Ref<number> = ref(0);
  const totalPages: Ref<number> = ref(1);

  const fetchTechnologies = async (page = 1, limit = 10, search = '') => {
    await withLoading(async () => {
      const response = await axios.get('/admin/technologies', {
        params: { page, limit, search }
      });
      technologies.value = response.data.technologies;
      totalItems.value = response.data.pagination.total_items;
      totalPages.value = response.data.pagination.total_pages;
    });
  };

  const fetchTechnologyById = async (id: number) => {
    await withLoading(async () => {
      const response = await axios.get(`/admin/technologies/${id}`);
      selectedTechnology.value = response.data;
    });
  };

  const saveTechnology = async (payload: Technology): Promise<void> => {
    await withLoading(async () => {
      const formData = new FormData();
      
      if (payload.id) formData.append('id', String(payload.id));      
      formData.append('name', payload.name);      
      if (payload.image instanceof File) formData.append('image', payload.image);

      await axios.post('/admin/technologies', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    });
  };

  const deleteTechnology = async (id: number): Promise<void> => {
    await withLoading(async () => {
      await axios.delete(`/admin/technologies/${id}`);
    });
  };

  return {
    technologies,
    selectedTechnology,
    totalItems,
    totalPages,
    isLoading,
    fetchTechnologies,
    fetchTechnologyById,
    saveTechnology,
    deleteTechnology
  };
});
