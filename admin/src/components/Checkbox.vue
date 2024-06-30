<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: {
    type: Boolean,
    required: true
  },
  modelValue: {
    type: Boolean,
    required: true
  },
  label: { 
    type: String, 
    default: '' 
  }
});

const emit = defineEmits(['update:modelValue']);

const model = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  },
});
</script>

<template>
  <label class="container text-gray-400 text-sm">
    <input
      v-model="model"
      :value="value"
      type="checkbox"
    >

    <span class="checkmark" />
      
    {{ label }}
  </label>
</template>

<style scoped>
/* Customize the label (the container) */
.container {
  display: flex;
  align-items: center;
  position: relative;
  padding-left: 25px;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default checkbox */
.container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 19px;
  width: 19px;
  border-radius: 3px;
  background-color: #fff;
  border: 1px solid #ddd;
  transition: .1s ease-in-out;
}

/* When the checkbox is checked, add a blue background */
.container input:checked ~ .checkmark {
  background-color: #55b984;
  border-color: #55b984;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

/* Show the checkmark when checked */
.container input:checked ~ .checkmark:after {
  display: block;
}

/* Style the checkmark/indicator */
.container .checkmark:after {
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 3px 3px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}
</style>