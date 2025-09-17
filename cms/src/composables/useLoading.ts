import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

interface ServerError {
  data?: {
    message?: string;
  };
  response?: {
    data?: {
      message?: string;
    };
  };
}

// Type guard para identificar erros de servidor
const isServerError = (error: unknown): error is ServerError => {
  return (
    typeof error === 'object' &&
    error !== null &&
    ('data' in error || 'response' in error)
  );
};

export function useLoading() {
  const isLoading = ref(false);
  
  const { showToast } = useToast();

  const showError = (error: unknown, fallbackMessage: string) => {
    if (isServerError(error)) {
      const message = error.response?.data?.message || error.data?.message;
      if (message) {
        showToast('error', message);
        return;
      }
    }

    if (error instanceof Error) {
      showToast('error', error.message || fallbackMessage);
      return;
    }

    showToast('error', fallbackMessage);
  };

  const withLoading = async <T>(
    asyncAction: () => Promise<T>,
    fallbackMessage = 'Ocorreu um erro ao processar a requisição. Tente novamente mais tarde.'
  ): Promise<T | undefined> => {
    isLoading.value = true;

    try {
      return await asyncAction();
    } catch (error) {
      showError(error, fallbackMessage);
      throw error;
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    withLoading,
  };
}
