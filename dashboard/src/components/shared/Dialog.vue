<template>
  <TransitionRoot
    appear
    :show="isOpen"
    as="template"
  >
    <Dialog
      as="div"
      @close="closeModal"
      class="relative z-10"
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
        <div class="fixed inset-0 bg-black/50" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div
          class="flex min-h-full items-center justify-center p-4 text-center"
        >
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-md transform overflow-hidden rounded-2xl bg-background p-6 text-left align-middle shadow-xl transition-all"
            >
              <DialogTitle
                as="h3"
                class="text-lg font-medium leading-6 text-font"
              >
                {{ header }}
              </DialogTitle>
              <div class="mt-2">
                <p class="text-sm text-secondary">
                  {{ message }}
                </p>
              </div>

              <div class="flex justify-start flex-row-reverse gap-3 mt-5">
                <button
                  type="button"
                  class="inline-flex justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-green-600 focus-visible:ring-offset-2"
                  @click="closeModal"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  class="inline-flex justify-center rounded-md border border-transparent bg-transparent px-4 py-2 text-sm font-medium text-red-500 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:transparent focus-visible:ring-offset-2"
                  @click="handleConfirm"
                >
                  Confirmar
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue';

const emit = defineEmits(['confirmAction']);

const props = defineProps({
  header: { 
    type: String, 
    default: '' 
  },
  message: { 
    type: String, 
    default: '' 
  },
});

const handleConfirm = () => {
  emit('confirmAction');
  closeModal();
};

const isOpen = ref(false);

const closeModal = () => {
  isOpen.value = false;
};
const openModal = () => {
  isOpen.value = true;
};

defineExpose({ openModal });
</script>
