<script setup lang="ts">
import { ref, watch, computed } from 'vue';

interface InputFileProps {
  label?: string;
  modelValue?: File | string | null;
  previewSize?: string;
  imagePath?: string;
}

const { label, modelValue, previewSize: previewSizeProp, imagePath } = defineProps<InputFileProps>();
const emit = defineEmits<{
  (e: 'update:modelValue', value: File | string | null): void;
  (e: 'clear'): void;
}>();

const API_URL = import.meta.env.VITE_API_URL;
const ALLOWED_TYPES = ['image/png', 'image/jpeg', 'image/webp', 'image/gif'];
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

const preview = ref<string | undefined>(undefined);
const fileInput = ref<HTMLInputElement | null>(null);
const errorMessage = ref<string | null>(null);

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;

  if (!file) {
    return;
  }

  if (!ALLOWED_TYPES.includes(file.type)) {
    errorMessage.value = 'Formato inválido. Permitido apenas PNG, JPG, WEBP ou GIF.';
    if (fileInput.value) {
      fileInput.value.value = '';
    }
    return;
  }

  if (file.size > MAX_FILE_SIZE) {
    errorMessage.value = 'O arquivo excede o limite máximo de 5MB.';
    if (fileInput.value) {
      fileInput.value.value = '';
    }
    return;
  }

  errorMessage.value = null;
  emit('update:modelValue', file);
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const clearImage = () => {
  errorMessage.value = null;
  preview.value = undefined;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
  emit('update:modelValue', null);
  emit('clear');
};

watch(
  () => modelValue,
  (val) => {
    if (val instanceof File) {
      const reader = new FileReader();
      reader.onload = (e) => (preview.value = e.target?.result as string);
      reader.readAsDataURL(val);
    } else if (typeof val === 'string' && val.trim() !== '') {
      preview.value = imagePath
        ? `${API_URL}/images/${imagePath}/${val}`
        : `${API_URL}/images/${val}`;
    } else {
      preview.value = undefined;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    }
  },
  { immediate: true }
);

const previewSize = computed(() => previewSizeProp ?? 'h-44 w-44');
</script>

<template>
  <div class="flex flex-col gap-2 relative w-full">
    <label v-if="label" class="text-gray-700 dark:text-gray-100 font-semibold">{{ label }}</label>

    <div class="relative inline-block w-fit">
      <div 
        :class="[
          'relative flex items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-success bg-transparent hover:bg-success-accent dark:hover:bg-success-accent-dark transition-colors cursor-pointer group overflow-hidden',
          previewSize
        ]" 
        @click="triggerFileInput"
      >
        <!-- Preview Image -->
        <img
          v-if="preview"
          :src="preview"
          alt="Preview"
          class="absolute inset-0 w-full h-full object-cover"
        >

        <!-- Overlay on Hover when there is a preview -->
        <div v-if="preview" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center gap-4 transition-opacity text-white text-xs">
          <button
            type="button"
            class="flex flex-col items-center gap-1 hover:text-emerald-400 transition-colors p-2 focus:outline-none"
            title="Alterar imagem"
            @click.stop="triggerFileInput"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
              <path d="m15 5 4 4" />
            </svg>
            <span>Alterar</span>
          </button>

          <button
            type="button"
            class="flex flex-col items-center gap-1 hover:text-red-400 transition-colors p-2 focus:outline-none"
            title="Remover imagem"
            @click.stop="clearImage"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="22"
              height="22"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M3 6h18" />
              <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
              <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
            </svg>
            <span>Remover</span>
          </button>
        </div>

        <!-- Add Icon (when no preview) -->
        <div v-else class="flex flex-col items-center justify-center text-gray-500 group-hover:text-success dark:group-hover:text-success-dark transition-colors">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="mb-3"
          >
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7" />
            <line x1="16" x2="22" y1="5" y2="5" />
            <line x1="19" x2="19" y1="2" y2="8" />
            <circle cx="9" cy="9" r="2" />
            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
          </svg>
          <span class="font-bold text-sm tracking-wider uppercase">ADICIONAR</span>
        </div>

        <!-- Hidden Input -->
        <input
          ref="fileInput"
          type="file"
          class="hidden"
          accept="image/png,image/jpeg,image/webp,image/gif"
          @change="handleFileChange"
        >
      </div>

      <!-- Delete Button (When preview is available) -->
      <button
        v-if="preview"
        type="button"
        class="absolute -top-2 -right-2 z-10 p-1.5 rounded-full bg-red-600 hover:bg-red-700 text-white shadow-md transition-all hover:scale-110 focus:outline-none focus:ring-2 focus:ring-red-400"
        title="Remover imagem"
        aria-label="Remover imagem"
        @click.stop="clearImage"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="14"
          height="14"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M18 6 6 18" />
          <path d="m6 6 12 12" />
        </svg>
      </button>
    </div>

    <p class="text-xs text-gray-400 dark:text-gray-500">
      PNG, JPG, WEBP ou GIF (máx. 5MB).
    </p>
    <p v-if="errorMessage" class="text-xs text-red-500 dark:text-red-400 font-medium">
      {{ errorMessage }}
    </p>
  </div>
</template>
