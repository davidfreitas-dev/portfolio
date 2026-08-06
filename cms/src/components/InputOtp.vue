<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
  label: {
    type: String,
    default: '',
  },
  modelValue: {
    type: String,
    default: '',
  },
  length: {
    type: Number,
    default: 6,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'complete']);

const digits = ref<string[]>(Array(props.length).fill(''));
const inputRefs = ref<HTMLInputElement[]>([]);

// Initialize digits from modelValue
watch(() => props.modelValue, (newVal) => {
  if (newVal !== digits.value.join('')) {
    const valDigits = newVal.split('').slice(0, props.length);
    digits.value = Array(props.length).fill('').map((_, i) => valDigits[i] || '');
  }
}, { immediate: true });

const handleInput = (index: number, event: Event) => {
  const input = event.target as HTMLInputElement;
  const value = input.value;
 
  // Only allow numbers
  const lastChar = value.slice(-1);
  if (lastChar && !/^\d$/.test(lastChar)) {
    digits.value[index] = '';
    return;
  }

  digits.value[index] = lastChar;
  emit('update:modelValue', digits.value.join(''));

  // Move to next input
  if (lastChar && index < props.length - 1) {
    inputRefs.value[index + 1].focus();
  }

  // Check if complete
  if (digits.value.every(d => d !== '') && digits.value.length === props.length) {
    emit('complete', digits.value.join(''));
  }
};

const handleKeyDown = (index: number, event: KeyboardEvent) => {
  if (event.key === 'Backspace') {
    if (!digits.value[index] && index > 0) {
      digits.value[index - 1] = '';
      inputRefs.value[index - 1].focus();
      emit('update:modelValue', digits.value.join(''));
    } else {
      digits.value[index] = '';
      emit('update:modelValue', digits.value.join(''));
    }
  } else if (event.key === 'ArrowLeft' && index > 0) {
    inputRefs.value[index - 1].focus();
  } else if (event.key === 'ArrowRight' && index < props.length - 1) {
    inputRefs.value[index + 1].focus();
  }
};

const handlePaste = (event: ClipboardEvent) => {
  event.preventDefault();
  const pasteData = event.clipboardData?.getData('text').slice(0, props.length) || '';
  const pasteDigits = pasteData.split('').filter(d => /^\d$/.test(d));

  pasteDigits.forEach((digit, i) => {
    if (i < props.length) {
      digits.value[i] = digit;
    }
  });

  emit('update:modelValue', digits.value.join(''));

  // Focus the next empty input or the last one
  const nextEmptyIndex = digits.value.findIndex(d => d === '');
  const focusIndex = nextEmptyIndex === -1 ? props.length - 1 : nextEmptyIndex;
  inputRefs.value[focusIndex].focus();

  if (digits.value.every(d => d !== '') && digits.value.length === props.length) {
    emit('complete', digits.value.join(''));
  }
};

onMounted(() => {
  // Focus first input if not disabled
  if (!props.disabled && inputRefs.value[0]) {
    inputRefs.value[0].focus();
  }
});
</script>

<template>
  <div class="flex flex-col gap-2 relative">
    <label v-if="label" class="text-gray-700 dark:text-gray-100 font-semibold">{{ label }}</label>
    <div class="flex justify-between gap-2 sm:gap-4">
      <input
        v-for="(_, index) in length"
        :key="index"
        ref="inputRefs"
        v-model="digits[index]"
        type="text"
        inputmode="numeric"
        maxlength="1"
        :disabled="disabled"
        class="text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 text-center text-2xl font-bold w-full h-[64px] rounded-xl focus:outline-none focus:ring-2 transition-all duration-200 disabled:cursor-not-allowed"
        :class="[
          error 
            ? 'border border-[var(--color-danger)] focus:ring-[var(--color-danger)] focus:border-[var(--color-danger)]'
            : 'border border-[var(--color-gray-200)] dark:border-gray-600 focus:ring-[var(--color-primary-default)] focus:border-[var(--color-primary-default)] dark:focus:ring-[var(--color-primary-default)]'
        ]"
        @input="handleInput(index, $event)"
        @keydown="handleKeyDown(index, $event)"
        @paste="handlePaste"
      >
    </div>
    <span v-if="error" class="text-[14px] text-[var(--color-danger)]">{{ error }}</span>
  </div>
</template>
