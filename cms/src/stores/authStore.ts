import { defineStore } from 'pinia';
import { ref, computed, type Ref } from 'vue';
import { useLoading } from '@/composables/useLoading';
import { useUserStore } from '@/stores/userStore';
import axios from '@/api/axios';
import type { User } from '@/types/user';

interface SignInPayload {
  login: string;
  password: string;
}

interface SignUpPayload {
  name: string;
  email: string;
  phone?: string;
  cpfcnpj?: string;
  password: string;
}

export const useAuthStore = defineStore('auth', () => {
  const userStore = useUserStore();

  const token: Ref<string | null> = ref(null);  
  const isAuthChecked: Ref<boolean> = ref(false);
  const isAuthenticated = computed(() => !!token.value);

  const { isLoading, withLoading } = useLoading();

  const setToken = (jwt: string) => {
    token.value = jwt;
  };

  const clearToken = () => {
    token.value = null;
    userStore.clearUser();
  };

  const initAuth = async () => {
    if (token.value) {
      try {
        const response = await axios.get('/users/me');
        const userData: User = response.data;
        userStore.setUser(userData);
      } catch {
        clearToken();
      }
    }
    isAuthChecked.value = true;
  };

  const signIn = async (payload: SignInPayload) => {
    await withLoading(async () => {
      const response = await axios.post('/auth/signin', payload);
      const jwt = response.data.token;
      setToken(jwt);
      const userData: User = await axios.get('/users/me');
      userStore.setUser(userData);
    });
  };

  const signUp = async (payload: SignUpPayload) => {
    await withLoading(async () => {
      const response = await axios.post('/auth/signup', payload);
      const jwt = response.data.token;
      setToken(jwt);
      const userData: User = await axios.get('/users/me');
      userStore.setUser(userData);
    });
  };

  const logOut = async () => {
    clearToken();
  };

  const forgotPassword = async (email: string) => {
    await withLoading(async () => {
      await axios.post('/auth/forgot', { email });
    });
  };

  const resetPassword = async (code: string, newPassword: string) => {
    await withLoading(async () => {
      await axios.post('/auth/reset', { code, password: newPassword });
    });
  };

  return {
    isLoading,
    isAuthChecked,
    isAuthenticated,
    token,
    setToken,
    initAuth,
    signIn,
    signUp,
    logOut,
    forgotPassword,
    resetPassword,
  };
}, {
  persist: {
    key: 'authToken',
    storage: localStorage,
    serializer: {
      serialize: (state) => JSON.stringify({
        token: state.token,
      }),
      deserialize: (str) => JSON.parse(str),
    },
  },
});
