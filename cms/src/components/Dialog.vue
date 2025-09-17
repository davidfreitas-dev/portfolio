<script setup lang="ts">
import { ref } from 'vue';
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';

defineProps<{
  header: string;
  message: string;
}>();

const emit = defineEmits<{
  (e: 'confirmAction'): void;
}>();

const isOpen = ref<boolean>(false);

const closeModal = (): void => {
  isOpen.value = false;
};

const openModal = (): void => {
  isOpen.value = true;
};

const handleConfirm = (): void => {
  emit('confirmAction');
  closeModal();
};

defineExpose({ openModal });
</script>

<template>
  <TransitionRoot
    appear
    :show="isOpen"
    as="template"
  >
    <Dialog
      as="div"
      class="relative z-10"
      @close="closeModal"
    >
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/50 dark:bg-black/80" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-accent dark:bg-background-dark p-6 text-left align-middle shadow-xl transition-all">
              <DialogTitle as="h3" class="text-lg font-semibold leading-6 text-font dark:text-font-dark">
                {{ header }}
              </DialogTitle>
              
              <div class="mt-2">
                <p class="text-sm text-secondary dark:text-secondary-dark">
                  {{ message }}
                </p>
              </div>

              <div class="flex justify-start flex-row-reverse gap-3 mt-5">
                <button
                  type="button"
                  class="inline-flex justify-center rounded-lg px-4 py-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium border-2 border-transparent focus-visible:border-primary focus-visible:dark:border-primary-dark focus-visible:ring-2 focus-visible:ring-primary-focus focus:outline-none cursor-pointer"
                  @click="handleConfirm"
                >
                  Confirmar
                </button>
                <button
                  type="button"
                  class="inline-flex justify-center rounded-lg px-4 py-2 bg-disabled/30 text-font dark:text-font-dark text-sm font-medium border-2 border-transparent focus-visible:ring-2 focus-visible:ring-secondary focus:outline-none cursor-pointer"
                  @click="closeModal"
                >
                  Cancelar
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
