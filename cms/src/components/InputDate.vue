<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';

const emit = defineEmits<{
 (e: 'update:modelValue', value: Date | null): void;
 (e: 'onKeyupEnter'): void;
}>();

const props = defineProps<{
 label?: string;
 floatingLabel?: boolean;
 placeholder?: string;
 modelValue: Date | null;
 disabled?: boolean;
 error?: string;
}>();

const MAX_DIGITS = 8;
const rawValue = ref('');
const formattedValue = ref('');

const isFocused = ref(false);

const isFloating = computed(() =>
  props.floatingLabel && (isFocused.value || !!props.modelValue)
);

const hasError = computed(() => !!props.error);

const labelClasses = computed(() => {
  if (!props.label) return [];

  const base = props.floatingLabel
    ? 'absolute left-4 transition-all duration-200 bg-white dark:bg-gray-800 pointer-events-none z-10 text-normal'
    : 'block mb-1.5 text-gray-700 dark:text-gray-100 font-semibold';

  const color = props.disabled
    ? 'text-gray-300 dark:text-gray-500'
    : props.floatingLabel && isFocused.value && hasError.value
      ? 'text-danger dark:text-danger-dark'
      : props.floatingLabel && isFocused.value
        ? 'text-primary-default '
        : props.floatingLabel
          ? 'text-gray-400 dark:text-gray-400'
          : '';

  const position = props.floatingLabel
    ? isFloating.value
      ? '-top-2 text-xs px-1'
      : 'top-1/2 -translate-y-1/2'
    : '';

  return [base, color, position];
});

const inputClasses = computed(() => {
  const base = 'w-full h-[52px] rounded-xl px-4 bg-transparent text-base text-gray-700 dark:text-gray-100 outline-none transition-all duration-200 border focus:ring-2';

  const stateClasses = hasError.value
    ? 'border-danger dark:border-danger-dark focus:ring-danger dark:focus:ring-danger-dark'
    : isFocused.value
      ? 'border-gray-300 dark:border-gray-600 focus:ring-primary-default '
      : 'border-gray-300 dark:border-gray-600';
 
  const placeholder = props.floatingLabel ? 'placeholder-transparent' : '';
 
  const disabled = props.disabled ? 'cursor-not-allowed opacity-60' : '';

  return [base, stateClasses, placeholder, disabled];
});

// Formata para "dd/MM/yyyy"
const formatDate = (value: string): string => {
  const cleanValue = value.replace(/[^\d]/g, '').slice(0, MAX_DIGITS);
  const day = cleanValue.slice(0, 2);
  const month = cleanValue.slice(2, 4);
  const year = cleanValue.slice(4, 8);
  return [day, month, year].filter(Boolean).join('/');
};

// Converte "dd/MM/yyyy"para Date (ou null)
const parseDate = (value: string): Date | null => {
  const [day, month, year] = value.split('/');
  if (day && month && year && day.length === 2 && month.length === 2 && year.length === 4) {
    const date = new Date(Number(year), Number(month) - 1, Number(day));
    return date.getDate() === Number(day) &&
 date.getMonth() === Number(month) - 1 &&
 date.getFullYear() === Number(year)
      ? date
      : null;
  }
  return null;
};

// Exibe a data formatada quando o modelValue muda
watch(
  () => props.modelValue,
  (newValue) => {
    if (newValue instanceof Date && !isNaN(newValue.getTime())) {
      const day = String(newValue.getDate()).padStart(2, '0');
      const month = String(newValue.getMonth() + 1).padStart(2, '0');
      const year = String(newValue.getFullYear());
      rawValue.value = day + month + year;
      formattedValue.value = formatDate(rawValue.value);
    } else {
      rawValue.value = '';
      formattedValue.value = '';
    }
  },
  { immediate: true }
);

onMounted(() => {
  if (props.modelValue instanceof Date) {
    const day = String(props.modelValue.getDate()).padStart(2, '0');
    const month = String(props.modelValue.getMonth() + 1).padStart(2, '0');
    const year = String(props.modelValue.getFullYear());
    rawValue.value = day + month + year;
    formattedValue.value = formatDate(rawValue.value);
  }
});

// Quando digita
const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
 
  let numericValue = target.value.replace(/[^\d]/g, '').slice(0, MAX_DIGITS);

  const day = numericValue.slice(0, 2);
  const month = numericValue.slice(2, 4);

  if (day.length === 2 && Number(day) > 31) numericValue = '31' + numericValue.slice(2);
  if (month.length === 2 && Number(month) > 12) numericValue = numericValue.slice(0, 2) + '12' + numericValue.slice(4);

  rawValue.value = numericValue;
  formattedValue.value = formatDate(numericValue);
  emit('update:modelValue', parseDate(formattedValue.value));
};

const allowedKeys = [
  'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'
];

const handleKeydown = (event: KeyboardEvent) => {
  const isDigit = /^\d$/.test(event.key);
  if (!isDigit && !allowedKeys.includes(event.key)) {
    event.preventDefault();
  }
};
</script>

<template>
  <div>
    <div class="relative w-full">
      <label v-if="label" :class="labelClasses">{{ label }}</label>

      <input
        type="text"
        maxlength="10"
        :value="formattedValue"
        :placeholder="placeholder || 'dd/MM/yyyy'"
        :disabled="disabled"
        :class="inputClasses"
        @input="handleInput"
        @focus="isFocused = true"
        @blur="isFocused = false"
        @keydown="handleKeydown"
        @keyup.enter="emit('onKeyupEnter')"
      >
    </div>
    <span v-if="error" class="text-sm text-danger dark:text-danger-dark">{{ error }}</span>
  </div>
</template>
