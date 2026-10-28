<script setup lang="ts">
import { ref } from 'vue';
import Icon from '@/components/Icon.vue';

const props = withDefaults(
  defineProps<{
    modelValue: string[];
    label?: string;
    placeholder?: string;
    error?: string;
    disabled?: boolean;
  }>(),
  {
    label: '',
    placeholder: 'Digite e aperte Enter ou vírgula...',
    error: '',
    disabled: false,
  }
);

const emit = defineEmits<{
  (e: 'update:modelValue', value: string[]): void;
  (e: 'blur'): void;
}>();

const newTag = ref('');
const inputRef = ref<HTMLInputElement | null>(null);

const addTag = () => {
  const trimmed = newTag.value.trim().replace(/^,+|,+$/g, '');
  if (!trimmed) {
    newTag.value = '';
    return;
  }

  // Se o usuário colou ou digitou múltiplos itens separados por vírgula
  const tagsToAdd = trimmed
    .split(',')
    .map(t => t.trim())
    .filter(t => t.length > 0);

  const current = [...props.modelValue];
  for (const tag of tagsToAdd) {
    if (!current.some(item => item.toLowerCase() === tag.toLowerCase())) {
      current.push(tag);
    }
  }

  emit('update:modelValue', current);
  newTag.value = '';
};

const removeTag = (index: number) => {
  if (props.disabled) return;
  const current = [...props.modelValue];
  current.splice(index, 1);
  emit('update:modelValue', current);
};

const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Enter' || event.key === ',') {
    event.preventDefault();
    addTag();
  } else if (event.key === 'Backspace' && newTag.value === '' && props.modelValue.length > 0) {
    removeTag(props.modelValue.length - 1);
  }
};

const focusInput = () => {
  inputRef.value?.focus();
};
</script>

<template>
  <div class="flex flex-col gap-2 relative">
    <label v-if="label" class="text-gray-700 dark:text-gray-100 font-semibold">{{ label }}</label>

    <div
      :class="[
        'flex flex-wrap items-center gap-2 min-h-[44px] rounded-lg px-3 py-2 bg-gray-100 dark:bg-gray-700 transition-all duration-200 cursor-text',
        error
          ? 'border border-danger dark:border-danger-dark focus-within:ring-1 focus-within:ring-danger'
          : 'border border-gray-200 dark:border-gray-600 focus-within:ring-1 focus-within:ring-primary-default focus-within:border-primary-default',
        disabled ? 'cursor-not-allowed opacity-60' : ''
      ]"
      @click="focusInput"
    >
      <span
        v-for="(tag, index) in modelValue"
        :key="tag + index"
        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300"
      >
        {{ tag }}
        <button
          type="button"
          :disabled="disabled"
          class="hover:text-blue-600 dark:hover:text-blue-100 focus:outline-none"
          @click.stop="removeTag(index)"
        >
          <Icon name="x" class="w-3.5 h-3.5" />
        </button>
      </span>

      <input
        ref="inputRef"
        v-model="newTag"
        type="text"
        :placeholder="modelValue.length === 0 ? placeholder : ''"
        :disabled="disabled"
        class="flex-1 min-w-[140px] bg-transparent text-gray-700 dark:text-gray-100 text-[14px] focus:outline-none disabled:cursor-not-allowed"
        @keydown="handleKeydown"
        @blur="addTag(); $emit('blur')"
      />
    </div>

    <p v-if="error" class="text-xs text-danger dark:text-danger-dark mt-0.5">
      {{ error }}
    </p>
  </div>
</template>
