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
              class="relative flex flex-col max-h-[calc(100dvh-2rem)] sm:max-h-[calc(100dvh-4rem)] w-[95%] md:w-[75%] lg:w-[65%] transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all my-auto sm:my-8"
            >
              <div class="modal-header flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 shrink-0">
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

              <div class="modal-body flex-1 min-h-0 flex flex-col overflow-y-auto">
                <slot />
              </div>

              <div
                v-if="$slots.footer"
                class="modal-footer flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 shrink-0"
              >
                <slot name="footer" />
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
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.modal-body::-webkit-scrollbar {
  width: 6px;
  background-color: transparent;
}

.modal-body::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 9999px;
}

:global(.dark) .modal-body::-webkit-scrollbar-thumb {
  background-color: #4b5563;
}
</style>
