<script setup lang="ts">
import { ref, computed } from 'vue';
import Icon from '@/components/Icon.vue';
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot
} from '@headlessui/vue';
import Button from '@/components/Button.vue';

const emit = defineEmits<{
 (e: 'onModalClose'): void;
}>();

const { title, align } = defineProps<{
 title: string;
 align?: 'top' | 'center' | 'bottom';
}>();

const open = ref(false);

const openModal = () => {
  open.value = true;
};

const closeModal = () => {
  emit('onModalClose');
  open.value = false;
};

export type ModalExpose = {
 openModal: () => void;
 closeModal: () => void;
};

defineExpose<ModalExpose>({
  openModal,
  closeModal
});

const alignmentClass = computed(() => {
  switch (align) {
  case 'top':
    return 'items-start';
  case 'bottom':
    return 'items-end';
  default:
    return 'items-center';
  }
});
</script>

<template>
  <TransitionRoot
    appear
    :show="open"
    as="template"
  >
    <Dialog
      as="div"
      class="relative z-50"
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

      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div
          class="flex min-h-full justify-center p-4 text-center sm:p-0"
          :class="alignmentClass"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 w-[95%] md:w-[75%] lg:w-[65%]"
            >
              <div class="p-6">
                <div class="sm:flex sm:items-start">
                  <div class="w-full">
                    <div class="modal-header flex justify-between items-center mb-5">
                      <DialogTitle as="h3" class="text-2xl font-semibold leading-6 text-gray-700 dark:text-gray-100">
                        {{ title }}
                      </DialogTitle>
 
                      <Button
                        variant="link"
                        color="secondary"
                        class="!p-1.5 ml-auto"
                        @click="closeModal"
                      >
                        <Icon name="close" class="text-xl" />
                        <span class="sr-only">Close modal</span>
                      </Button>
                    </div>

                    <div class="modal-body overflow-y-auto p-0.5">
                      <slot />
                    </div>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<style scoped>
.modal-body {
 overflow-y: auto; /* Garante que o scroll vertical funcione */
 -webkit-overflow-scrolling: touch; /* Melhora o desempenho de rolagem em dispositivos móveis */
}

/* Oculta a barra de rolagem */
.modal-body::-webkit-scrollbar {
 display: none;
}
</style>
