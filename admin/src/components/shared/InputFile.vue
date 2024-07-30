<script setup>
const props = defineProps({
  label: {
    type: String, 
    default: '' 
  },
  modelValue: { 
    type: Object, 
    default: () => {} 
  },
});

const emit = defineEmits(['update:modelValue']);

const handleFileChange = (event) => {
  const file = event.target.files[0];
  
  emit('update:modelValue', file);
};
</script>

<template>
  <label class="font-semibold">
    {{ label }}
  </label>
  
  <input
    type="file"
    ref="photoInput"
    @change="handleFileChange"
  >
</template>

<style scoped>
input[type=file]::file-selector-button {
  color: #fff;
  background: #a6a6a6;
  border: none;
  border-radius: 6px;
  height: 40px;
  padding: 0 1rem;
  margin-right: 20px;
  cursor: pointer;
  transition: background .2s ease-in-out;
}

input[type=file]::file-selector-button:hover {
  background: #6ec496;
}
</style>