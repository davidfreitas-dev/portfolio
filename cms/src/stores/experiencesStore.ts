import { defineStore } from 'pinia';
import { ref, type Ref } from 'vue';
import { useLoading } from '@/composables/useLoading';
import axios from '@/api/axios';

export interface Experience {
  id?: number;
  title: string;
  description: string;
  start_date: string;
  end_date: string;
}

export const useExperiencesStore = defineStore('experiences', () => {
  const { isLoading, withLoading } = useLoading();
  const experiences: Ref<Experience[]> = ref([]);
  const selectedExperience: Ref<Experience | null> = ref(null);
  const totalItems: Ref<number> = ref(0);
  const totalPages: Ref<number> = ref(1);

  const fetchExperiences = async (page = 1, limit = 10, search = '') => {
    await withLoading(async () => {
      const response = await axios.get('/experiences', {
        params: { page, limit, search }
      });
      experiences.value = response.data.experiences;
      totalItems.value = response.data.total;
      totalPages.value = response.data.pages;
    });
  };

  const fetchExperienceById = async (id: number) => {
    await withLoading(async () => {
      const response = await axios.get(`/experiences/${id}`);
      selectedExperience.value = response.data;
    });
  };

  const createExperience = async (payload: Experience): Promise<void> => {
    await withLoading(async () => {
      await axios.post('/experiences', payload);
    });
  };

  const updateExperience = async (id: number, payload: Experience): Promise<void> => {
    await withLoading(async () => {
      await axios.put(`/experiences/${id}`, payload);
    });
  };

  const deleteExperience = async (id: number): Promise<void> => {
    await withLoading(async () => {
      await axios.delete(`/experiences/${id}`);      
    });      
  };

  return {
    experiences,
    selectedExperience,
    totalItems,
    totalPages,
    isLoading,
    fetchExperiences,
    fetchExperienceById,
    createExperience,
    updateExperience,
    deleteExperience
  };
});
