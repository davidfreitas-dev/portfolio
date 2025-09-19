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
}>();

const API_URL = import.meta.env.VITE_API_URL;

const preview = ref<string | undefined>(undefined);

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;
  emit('update:modelValue', file);
};

watch(
  () => modelValue,
  (val) => {
    if (val instanceof File) {
      const reader = new FileReader();
      reader.onload = (e) => (preview.value = e.target?.result as string);
      reader.readAsDataURL(val);
    } else if (typeof val === 'string') {
      preview.value = imagePath
        ? `${API_URL}/images/${imagePath}/${val}`
        : `${API_URL}/images/${val}`;
    } else {
      preview.value = undefined;
    }
  },
  { immediate: true }
);

const previewSize = computed(() => previewSizeProp ?? 'h-44 w-44');
</script>

<template>
  <label class="text-font dark:text-font-dark font-semibold">{{ label }}</label>

  <div v-if="preview" :class="`${previewSize} mt-2`">
    <img
      :src="preview"
      alt="Preview"
      class="object-contain rounded-lg"
    >
  </div>

  <input
    type="file"
    class="mt-2"
    @change="handleFileChange"
  >

  <p class="text-sm text-secondary dark:text-secondary mt-1">
    SVG, PNG, JPG or GIF (MAX. 800x800px).
  </p>
</template>

<style scoped>
input[type=file]::file-selector-button {
  color: #fff;
  background: #bbbbbb;
  border: none;
  border-radius: 6px;
  height: 40px;
  padding: 0 1rem;
  margin-right: 20px;
  cursor: pointer;
  transition: background .2s ease-in-out;
}

input[type=file]::file-selector-button:hover {
  background: #01c38d;
}
</style>
