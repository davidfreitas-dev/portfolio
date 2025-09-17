<script setup lang="ts">
import { ref, watch, toRefs } from 'vue';
import {
  Listbox,
  ListboxButton,
  ListboxOptions,
  ListboxOption,
} from '@headlessui/vue';
import { type Option } from '@/types/option';
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
    <label v-if="props.label" class="text-font font-semibold dark:text-font-dark">
      {{ props.label }}
    </label>

    <Listbox v-slot="{ open }" v-model="selectedOption">
      <div class="relative w-full">
        <ListboxButton
          :class="[
            'flex items-center gap-3 h-[52px] w-full p-4 bg-transparent rounded-xl text-base text-left placeholder:text-secondary focus:outline-none focus:ring-2',
            error
              ? 'border border-danger focus:ring-danger'
              : 'border border-neutral text-font dark:border-neutral-dark dark:text-font-dark dark:placeholder:text-secondary-dark focus:ring-primary'
          ]"
        >
          <span class="flex-1 truncate text-font dark:text-font-dark">
            {{ selectedOption?.label || 'Selecione uma opção' }}
          </span>
          <Icon
            name="keyboard_arrow_down"
            class="text-font dark:text-font-dark transform transition-transform duration-200"
            :class="{ 'rotate-180': open }"
          />
        </ListboxButton>

        <transition
          leave-active-class="transition duration-100 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <ListboxOptions
            class="absolute mt-1.5 max-h-60 w-full overflow-auto rounded-xl bg-white dark:bg-background-dark text-base shadow-lg focus:outline-none border border-neutral dark:border-neutral-dark z-10"
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
                  active ? 'bg-accent/50 dark:bg-accent-dark/40 text-font dark:text-font-dark' : 'text-secondary dark:text-secondary-dark',
                  'relative cursor-pointer select-none py-4 pl-12 pr-4',
                ]"
              >
                <span
                  :class="[
                    selected ? 'font-semibold text-font dark:text-font-dark' : 'font-normal',
                    'block truncate',
                  ]"
                >
                  {{ option.label }}
                </span>
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary"
                >
                  <Icon name="check" />
                </span>
              </li>
            </ListboxOption>
          </ListboxOptions>
        </transition>
      </div>
    </Listbox>

    <span v-if="error" class="text-sm text-danger">{{ error }}</span>
  </div>
</template>
