import { defineStore } from 'pinia';
import { ref, type Ref } from 'vue';
import axios from '@/api/axios';
import type { User } from '@/types/user';
import { useAuthStore } from './authStore';

export const useUserStore = defineStore('user', () => {
  const authStore = useAuthStore();

  const user: Ref<User | null> = ref(null);

  const setUser = (userData: User): void => {
    user.value = userData;
  };

  const clearUser = (): void => {
    user.value = null;
  };

  const fetchUser = async (): Promise<void> => {
    try {
      const response = await axios.get('/users/me');
      const userData: User = response.data;
      setUser(userData);
    } catch (error) {
      console.error('Erro ao buscar usuário:', error);
      clearUser();
      throw error;
    }
  };

  const updateUser = async (updatedData: Partial<User>): Promise<void> => {
    try {
      const response = await axios.post('/users/me', updatedData);
      const jwt = response.data.token;
      authStore.setToken(jwt);
      fetchUser();
    } catch (error) {
      console.error('Erro ao atualizar usuário:', error);
      throw error;
    }
  };

  return {
    user,
    setUser,
    clearUser,
    fetchUser,
    updateUser
  };
});
