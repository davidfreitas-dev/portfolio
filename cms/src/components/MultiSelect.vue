<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

export interface Option {
  id: number;
  name: string;
}

const props = defineProps<{
  label?: string;
  modelValue: Option[]; // v-model
  options: { label: string; value: number }[]; // opções brutas
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: Option[]): void;
}>();

const isFocused = ref(false);
const isDropdownOpen = ref(false);
const selectboxRef = ref<HTMLDivElement | null>(null);

// Converte opções para o formato { id, name }
const formattedOptions = computed<Option[]>(() =>
  props.options.map(opt => ({ id: opt.value, name: opt.label }))
);

// Estado interno do select
const selectedOptions = ref<Option[]>([...props.modelValue]);

// Sincroniza mudanças externas
watch(() => props.modelValue, (newVal) => {
  selectedOptions.value = [...newVal];
});

// Seleciona ou remove uma opção
const selectOption = (option: Option) => {
  const index = selectedOptions.value.findIndex(o => o.id === option.id);
  if (index > -1) selectedOptions.value.splice(index, 1);
  else selectedOptions.value.push(option);

  emit('update:modelValue', [...selectedOptions.value]);
};

// Verifica se a opção está selecionada
const isOptionSelected = (option: Option) =>
  selectedOptions.value.some(o => o.id === option.id);

// Toggle dropdown
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
  isFocused.value = isDropdownOpen.value;
};

// Evita blur ao clicar no dropdown
const preventBlur = (event: MouseEvent) => {
  event.preventDefault();
};

// Fecha dropdown ao clicar fora
const handleClickOutside = (event: MouseEvent) => {
  if (selectboxRef.value && !selectboxRef.value.contains(event.target as Node)) {
    isDropdownOpen.value = false;
    isFocused.value = false;
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
});
</script>

<template>
  <div ref="selectboxRef" class="flex flex-col gap-2 relative">
    <label class="text-font dark:text-font-dark font-semibold">{{ label }}</label>

    <div
      class="flex flex-wrap items-center justify-between border border-neutral dark:border-neutral-dark text-font dark:text-font-dark bg-transparent text-base w-full h-[52px] rounded-xl px-4 focus:outline-none focus:ring-2 transition-all duration-200 cursor-pointer disabled:cursor-not-allowed"
      :class="{'border-primary dark:border-primary-dark': isFocused}"
      @click="toggleDropdown"
    >
      <div class="flex flex-wrap gap-2 items-center flex-grow">
        <template v-if="selectedOptions.length">
          <template v-for="opt in selectedOptions" :key="opt.id">
            <span class="flex items-center gap-2 bg-primary-accent dark:bg-primary-accent-dark py-1.5 px-4 rounded-full">
              {{ opt.name }}
              <span
                class="material-icons text-base cursor-pointer"
                @click.stop="selectOption(opt)"
              >
                close
              </span>
            </span>
          </template>
        </template>
        <template v-else>
          Selecione
        </template>
      </div>

      <div class="chevrons">
        <span class="material-icons pt-2">
          {{ isDropdownOpen ? 'keyboard_arrow_up' : 'keyboard_arrow_down' }}
        </span>
      </div>
    </div>

    <ul
      v-if="isDropdownOpen"
      class="absolute top-full left-0 z-10 w-full max-h-[170px] overflow-y-auto mt-2 rounded-lg border border-neutral dark:border-neutral-dark bg-background dark:bg-background-dark cursor-pointer"
      @mousedown="preventBlur"
    >
      <li
        v-for="(option, index) in formattedOptions"
        :key="option.id"
        class="flex items-center gap-2 p-4 text-secondary dark:text-secondary-dark"
        :class="{'border-b border-neutral dark:border-neutral-dark': index !== formattedOptions.length - 1, 'bg-background dark:bg-background-dark': isOptionSelected(option)}"
        @click="selectOption(option)"
      >
        <span class="material-icons text-primary">
          {{ isOptionSelected(option) ? 'check_box' : 'check_box_outline_blank' }}
        </span>
        {{ option.name }}
      </li>
    </ul>
  </div>
</template>
