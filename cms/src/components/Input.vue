<script setup lang="ts">
import { ref, computed } from 'vue';
import { type PropType } from 'vue';
import Icon from '@/components/Icon.vue';

const emit = defineEmits<{
 (event: 'update:modelValue', value: string | number): void;
 (event: 'onKeyupEnter'): void;
 (event: 'blur', e: FocusEvent): void;
}>();

const { disabled, type, label, placeholder, modelValue, error } = defineProps({
  disabled: { 
    type: Boolean, 
    default: false 
  },
  type: { 
    type: String, 
    default: '' 
  },
  label: { 
    type: String, 
    default: '' 
  },
  placeholder: { 
    type: String, 
    default: '' 
  },
  modelValue: {
    type: [String, Number] as PropType<string | number>,
    default: null
  },
  error: { 
    type: String, 
    default: '' 
  }
});

const showPassword = ref(false);

const inputType = computed(() =>
  type === 'password' ? (showPassword.value ? 'text' : 'password') : type
);

const updateValue = (event: Event) => {
  const input = event.target as HTMLInputElement;
  emit('update:modelValue', input.value);
};
</script>

<template>
  <div class="flex flex-col gap-2 relative">
    <label v-if="label" class="text-gray-700 dark:text-gray-100 font-semibold">{{ label }}</label>

    <div class="relative">
      <input
        :type="inputType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'text-gray-700 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 text-[14px] w-full h-[44px] rounded-lg px-4 focus:outline-none focus:ring-1 transition-all duration-200 disabled:cursor-not-allowed',
          error
            ? 'border border-[var(--color-danger)] focus:ring-[var(--color-danger)] focus:border-[var(--color-danger)]'
            : 'border border-[var(--color-gray-200)] dark:border-gray-600 focus:ring-[var(--color-primary-default)] focus:border-[var(--color-primary-default)] dark:focus:ring-[var(--color-primary-default)]'
        ]"
        @input="updateValue"
        @keyup.enter="$emit('onKeyupEnter')"
        @blur="$emit('blur', $event)"
      >

      <button
        v-if="type === 'password'"
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 h-6 w-6 text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-100 cursor-pointer"
        @click="showPassword = !showPassword"
      >
        <Icon :name="showPassword ? 'visibility_off' : 'visibility'" />
      </button>
    </div>

    <span v-if="error" class="text-[14px] text-[var(--color-danger)]">{{ error }}</span>
  </div>
</template>
