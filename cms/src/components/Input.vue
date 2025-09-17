<script setup lang="ts">
import { ref, computed } from 'vue';
import { type PropType } from 'vue';
import Icon from '@/components/Icon.vue';

const emit = defineEmits<{
  (event: 'update:modelValue', value: string | number): void;
  (event: 'onKeyupEnter'): void;
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
    <label v-if="label" class="text-font dark:text-font-dark font-semibold">{{ label }}</label>

    <div class="relative">
      <input
        :type="inputType"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="[
          'text-font dark:text-font-dark bg-transparent text-base w-full h-[52px] rounded-xl px-4 focus:outline-none focus:ring-2 transition-all duration-200 disabled:cursor-not-allowed',
          error
            ? 'border border-danger focus:ring-danger'
            : 'border border-neutral dark:border-neutral-dark focus:ring-primary dark:focus:ring-primary'
        ]"
        @input="updateValue"
        @keyup.enter="$emit('onKeyupEnter')"
      >

      <button
        v-if="type === 'password'"
        type="button"
        class="absolute right-3 top-1/2 -translate-y-1/2 h-6 w-6 text-secondary dark:text-secondary-dark cursor-pointer"
        @click="showPassword = !showPassword"
      >
        <Icon :name="showPassword ? 'visibility_off' : 'visibility'" />
      </button>
    </div>

    <span v-if="error" class="text-sm text-danger">{{ error }}</span>
  </div>
</template>
