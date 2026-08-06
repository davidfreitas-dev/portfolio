import { defineStore } from 'pinia';
import { ref, type Ref } from 'vue';
import { experienceService, type CreateExperiencePayload, type UpdateExperiencePayload } from '@/services/experienceService';
import type { Experience } from '@/types';

export const useExperiencesStore = defineStore('experiences', () => {
  const experiences: Ref<Experience[]> = ref([]);
  const selectedExperience: Ref<Experience | null> = ref(null);
  const totalItems: Ref<number> = ref(0);
  const totalPages: Ref<number> = ref(1);

  const fetchExperiences = async (page = 1, limit = 10, search = '') => {
    const data = await experienceService.fetchExperiences(page, limit, search);
    experiences.value = data.experiences || [];
    totalItems.value = data.total ?? 0;
    totalPages.value = data.pages ?? 1;
  };

  const fetchExperienceById = async (id: number) => {
    const data = await experienceService.fetchExperienceById(id);
    selectedExperience.value = data;
  };

  const createExperience = async (payload: CreateExperiencePayload): Promise<void> => {
    await experienceService.createExperience(payload);
  };

  const updateExperience = async (id: number, payload: UpdateExperiencePayload): Promise<void> => {
    await experienceService.updateExperience(id, payload);
  };

  const deleteExperience = async (id: number): Promise<void> => {
    await experienceService.deleteExperience(id);      
  };

  return {
    experiences,
    selectedExperience,
    totalItems,
    totalPages,
    fetchExperiences,
    fetchExperienceById,
    createExperience,
    updateExperience,
    deleteExperience
  };
});
