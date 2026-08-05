import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { profileService } from '@/services/profileService';
import { useAuthStore } from '@/stores/authStore';
import type { UpdateProfilePayload, UpdatePasswordPayload } from '@/services/profileService';
import type { UserProfile } from '@/types';

export const useProfileStore = defineStore('profile', () => {
  const user = ref<UserProfile | null>(null);
  const isLoading = ref(false);

  const isAdmin = computed(() => user.value?.role === 'admin');

  async function fetchProfile(): Promise<UserProfile | null> {
    const authStore = useAuthStore();
    if (!authStore.isAuthenticated) return null;

    isLoading.value = true;
    try {
      const data = await profileService.fetchProfile();
      user.value = data;
      return data;
    } catch {
      authStore.clearSession();
      user.value = null;
      return null;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateProfile(payload: UpdateProfilePayload): Promise<UserProfile | null> {
    isLoading.value = true;
    try {
      await profileService.updateProfile(payload);
      return await fetchProfile(); // recarrega os dados completos após atualizar
    } finally {
      isLoading.value = false;
    }
  }

  async function updatePassword(payload: UpdatePasswordPayload): Promise<void> {
    isLoading.value = true;
    try {
      await profileService.updatePassword(payload);
    } finally {
      isLoading.value = false;
    }
  }


  function clearProfile(): void {
    user.value = null;
  }

  return {
    user,
    isLoading,
    isAdmin,
    fetchProfile,
    updateProfile,
    updatePassword,
    clearProfile,
  };
});
