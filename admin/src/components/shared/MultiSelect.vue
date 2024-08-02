<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  selected: {
    type: Array,
    default: () => []
  },
  options: {
    type: Array,
    default: () => []
  },
});

const isFocused = ref(false);
const isDropdownOpen = ref(false);
const selectedOptions = ref([...props.selected]);
const selectboxRef = ref(null);

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
  isFocused.value = isDropdownOpen.value;
};

const isOptionSelected = (option) => {
  return selectedOptions.value.some((opt) => opt.id === option.id);
};

const emit = defineEmits(['handleSelectChange']);

const selectOption = (option) => {
  const optionIndex = selectedOptions.value.findIndex(opt => opt.id === option.id);
  
  if (optionIndex > -1) {
    selectedOptions.value.splice(optionIndex, 1);
  } else {
    selectedOptions.value.push(option);
  }

  emit('handleSelectChange', selectedOptions.value);
};

const preventBlur = (event) => {
  event.preventDefault();
};

const handleClickOutside = (event) => {
  if (selectboxRef.value && !selectboxRef.value.contains(event.target)) {
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
  <div ref="selectboxRef" class="relative text-font">
    <label class="text-font font-semibold">
      {{ label }}
    </label>

    <div
      :class="['flex items-center justify-between bg-white border text-base w-full h-[52px] rounded-lg px-4 cursor-pointer disabled:cursor-not-allowed', { 'border-2 border-primary': isFocused, 'mt-2 mb-5': label }]"
      @click="toggleDropdown"
    >
      <div class="flex items-start justify-center">
        <template v-if="selectedOptions.length">
          <template v-for="(selectedOption) in selectedOptions" :key="selectedOption">
            <span class="flex items-center gap-2 bg-primary-accent py-1.5 px-4 rounded-full mr-2">
              {{ selectedOption.name }}
              <span class="material-icons text-base" @click="(event) => { event.stopPropagation(); selectOption(selectedOption); }">
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
      class="absolute top-full left-0 z-10 w-full max-h-[170px] overflow-y-auto mt-2 rounded-lg border border-gray-300 bg-white cursor-pointer"
      @mousedown="preventBlur"
    >
      <li
        v-for="(option, index) in options"
        :key="option"
        :class="{ 'border-b border-gray-300': index !== options.length - 1, 'bg-gray-50': selectedOptions.includes(option) }"
        class="flex items-center gap-2 p-4 text-secondary"
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
