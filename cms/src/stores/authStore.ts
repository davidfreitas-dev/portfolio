import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { authService } from '@/services/authService';
import { useProfileStore } from '@/stores/profileStore';
import type {
  LoginCredentials,
  ForgotPasswordPayload,
  ResetPasswordPayload,
  ValidateResetCodePayload,
  RequestLoginPayload,
} from '@/types';

export class UnauthorizedRoleError extends Error {
  isUnauthorizedRole = true;
  constructor(message: string) {
    super(message);
    this.name = 'UnauthorizedRoleError';
  }
}

export const useAuthStore = defineStore('auth', () => {
  // Stores
  const profileStore = useProfileStore();

  // State
  const accessToken = ref<string | null>(null);
  const isLoading = ref(false);
  const isHydrated = ref(false);

  // Getters
  const isAuthenticated = computed(() => !!accessToken.value);

  // Actions
  function setToken(access: string): void {
    accessToken.value = access;
  }

  function clearSession(): void {
    accessToken.value = null;
    profileStore.clearProfile();
  }

  async function hydrate(): Promise<boolean> {
    if (isHydrated.value && profileStore.user) return isAuthenticated.value;
    
    // Se não há token em memória (e não está sendo carregado por persistência), não estamos autenticados.
    // Como a API não possui endpoint de refresh, apenas validamos se o perfil consegue ser obtido.
    if (!accessToken.value) {
      isHydrated.value = true;
      return false;
    }

    try {
      const profile = await profileStore.fetchProfile();
      if (profile?.role === 'customer' || profile?.role === 'user') {
        clearSession();
        throw new UnauthorizedRoleError('Acesso não autorizado para esta role.');
      }
      return true;
    } catch {
      clearSession();
      return false;
    } finally {
      isHydrated.value = true;
    }
  }

  async function login(credentials: LoginCredentials): Promise<void> {
    isLoading.value = true;
    try {
      const data = await authService.login(credentials);
      setToken(data.token);

      // Busca o perfil imediatamente para verificar a role
      const profile = await profileStore.fetchProfile();
      
      if (profile?.role === 'customer' || profile?.role === 'user') {
        clearSession();
        throw new UnauthorizedRoleError('Acesso não autorizado para esta role.');
      }
    } finally {
      isLoading.value = false;
    }
  }

  async function logout(): Promise<void> {
    isLoading.value = true;
    try {
      await authService.logout();
    } catch {
      // Ignora erro de rede no logout
    } finally {
      clearSession();
      isLoading.value = false;
    }
  }

  async function requestLogin(payload: RequestLoginPayload): Promise<void> {
    await authService.requestLogin(payload);
  }

  async function forgotPassword(payload: ForgotPasswordPayload): Promise<void> {
    await authService.forgotPassword(payload);
  }

  async function validateResetCode(payload: ValidateResetCodePayload): Promise<void> {
    await authService.validateResetCode(payload);
  }

  async function resetPassword(payload: ResetPasswordPayload): Promise<void> {
    await authService.resetPassword(payload);
  }

  return {
    accessToken,
    isLoading,
    isHydrated,
    isAuthenticated,
    setToken,
    clearSession,
    hydrate,
    requestLogin,
    login,
    logout,
    forgotPassword,
    validateResetCode,
    resetPassword,
  };
}, {
  persist: {
    pick: ['accessToken']
  }
});
