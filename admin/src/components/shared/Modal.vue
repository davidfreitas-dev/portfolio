<script setup>
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const emit = defineEmits(['onModalClose']);

const props = defineProps({
  title: { 
    type: String, 
    default: 'Modal Title' 
  }
});

const open = ref(false);

const setClose = () => {
  open.value = false;
};

const setOpen = () => {
  open.value = true;
};

const closeModal = () => {
  emit('onModalClose');
  setClose();
};

defineExpose({
  setOpen,
  closeModal
});
</script>

<template>
  <TransitionRoot
    as="template"
    :show="open"
  >
    <Dialog
      as="div"
      class="relative z-10"
      @close="closeModal"
    >
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-start justify-center p-4 text-center sm:items-start sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-[95%] md:w-[75%] lg:w-[65%]">
              <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mt-3 w-full px-5">
                    <div class="modal-header flex justify-between items-center">
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-semibold leading-6 text-gray-900"
                      >
                        {{ title }}
                      </DialogTitle>
                      <button
                        type="button"
                        class="text-gray-400 bg-transparent hover:border-brand hover:text-brand rounded-lg text-sm p-1.5 ml-auto inline-flex items-center outline-none"
                        data-modal-hide="defaultModal"
                        @click="closeModal"
                      >
                        <svg
                          aria-hidden="true"
                          class="w-5 h-5"
                          fill="currentColor"
                          viewBox="0 0 20 20"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                        <span class="sr-only">Close modal</span>
                      </button>
                    </div>

                    <div class="modal-body overflow-y-auto my-5 px-0.5">
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