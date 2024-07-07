<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  selected: {
    type: String,
    default: ''
  },
  options: {
    type: Array,
    default: () => []
  },
});

const isFocused = ref(false);
const isDropdownOpen = ref(false);
const selectedOption = ref(props.selected);
const selectboxRef = ref(null);

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
  isFocused.value = isDropdownOpen.value;
};

const emit = defineEmits(['handleSelectChange']);

const selectOption = (option) => {
  emit('handleSelectChange', option);
  selectedOption.value = option;
  isDropdownOpen.value = false;
  isFocused.value = false;
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
      {{ selectedOption || 'Selecione' }}
      
      <div class="chevrons">
        <span class="material-icons  pt-2">
          {{ isDropdownOpen ? 'keyboard_arrow_down' : 'keyboard_arrow_up' }}
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
        :class="{ 'border-b border-gray-300': index !== options.length - 1 }"
        class="flex items-center gap-2 p-4 text-font"
        @click="selectOption(option)"
      >
        {{ option }}
      </li>
    </ul>
  </div>
</template>
