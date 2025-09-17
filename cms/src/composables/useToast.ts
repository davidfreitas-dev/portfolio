import { ref, type Ref } from 'vue';
import type { ToastData, ToastType } from '@/types/toast';

interface ToastComponent {
  showToast: () => void;
}

const toast: Ref<ToastComponent | null> = ref(null);

const toastData: Ref<ToastData> = ref({
  message: '',
  type: 'error',
});

const showToast = (type: ToastType, message: string): void => {
  toastData.value.type = type;
  toastData.value.message = message;
  toast.value?.showToast();
};

export function useToast() {
  return {
    toast,
    toastData,
    showToast,
  };
}
