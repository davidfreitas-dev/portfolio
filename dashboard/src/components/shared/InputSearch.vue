<script setup>
const props = defineProps({
  modelValue: { 
    type: String, 
    default: '' 
  },
  placeholder: { 
    type: String, 
    default: 'O que você busca?' 
  }
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (event) => {
  emit('update:modelValue', event.target.value);
};
</script>

<template>
  <div class="flex items-center gap-3 h-12 w-[70%] md:w-[50%] lg:w-[35%] p-4 bg-transparent border border-accent focus-within:border-none rounded-md focus-within:ring-2 ring-primary-hover">
    <input
      type="text"
      class="flex-1 bg-transparent text-base text-font placeholder:text-secondary outline-none"
      :value="modelValue"
      :placeholder="placeholder"
      @input="updateValue"
    >

    <span class="material-icons w-5 h-5 text-secondary">
      search
    </span>
  </div>
</template>