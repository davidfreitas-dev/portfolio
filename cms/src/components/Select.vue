<script setup lang="ts">
import { ref, watch, toRefs } from 'vue';
import {
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue';
import { type Option } from '@/types';
import Icon from '@/components/Icon.vue';

const props = defineProps<{
 options: Option[];
 modelValue: Option | null;
 label?: string;
 error?: string;
}>();

const emit = defineEmits<{
 (e: 'update:modelValue', value: Option | null): void;
}>();

const { modelValue, error } = toRefs(props);

const selectedOption = ref<Option | null>(modelValue.value ?? null);

watch(modelValue, (newValue) => {
  selectedOption.value = newValue;
});

watch(selectedOption, (newValue) => {
  emit('update:modelValue', newValue);
});
</script>

<template>
  <div class="flex flex-col gap-1 relative w-full">
    <label v-if="props.label" class="text-gray-700 font-semibold dark:text-gray-100">
      {{ props.label }}
    </label>

    <Listbox v-slot="{ open }" v-model="selectedOption">
      <div class="relative w-full">
        <ListboxButton
          :class="[
            'flex items-center gap-3 h-[44px] w-full px-4 py-2 bg-transparent rounded-lg text-[14px] text-left placeholder:text-gray-400 focus:outline-none focus:ring-1',
            error
              ? 'border border-danger focus:ring-danger focus:border-danger'
              : 'border border-gray-300 text-gray-700 dark:border-gray-600 dark:text-gray-100 dark:placeholder:text-gray-400 focus:ring-primary-default focus:border-primary-default'
          ]"
        >
          <span class="flex-1 truncate text-gray-700 dark:text-gray-100">
            {{ selectedOption?.label || 'Selecione uma opção' }}
          </span>
          <Icon
            name="keyboard_arrow_down"
            class="text-gray-700 dark:text-gray-100 transform transition-transform duration-200"
            :class="{ 'rotate-180': open }"
          />
        </ListboxButton>

        <transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <ListboxOptions
            class="absolute mt-1.5 max-h-60 w-full overflow-auto rounded-lg bg-white dark:bg-gray-800 text-[14px] shadow-lg focus:outline-none border border-gray-300 dark:border-gray-600 z-10"
          >
            <ListboxOption
              v-for="option in props.options"
              :key="option.value"
              v-slot="{ active, selected }"
              :value="option"
              as="template"
            >
              <li
                :class="[
                  active ? 'bg-gray-100 dark:bg-gray-600 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-100',
                  'relative cursor-pointer select-none py-4 pl-12 pr-4 transition-colors duration-150',
                ]"
              >
                <span
                  :class="[
                    selected ? 'font-semibold' : 'font-normal',
                    'block truncate',
                  ]"
                >
                  {{ option.label }}
                </span>
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary-default"
                >
                  <Icon name="check" />
                </span>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>

    <span v-if="error" class="text-[14px] text-danger">{{ error }}</span>
  </div>
</template>
