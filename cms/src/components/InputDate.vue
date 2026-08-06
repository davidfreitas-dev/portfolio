<script setup lang="ts">
import { computed } from 'vue';
import { VueDatePicker } from '@vuepic/vue-datepicker';
import { useDark } from '@vueuse/core';
import { ptBR } from 'date-fns/locale';
import dayjs from 'dayjs';
import '@vuepic/vue-datepicker/dist/main.css';

interface TimeValue {
  hours: number;
  minutes: number;
  seconds?: number;
}

const props = withDefaults(defineProps<{
  label?: string;
  placeholder?: string;
  modelValue: Date | Date[] | null;
  disabled?: boolean;
  error?: string;
  mode?: 'date' | 'range' | 'time';
}>(), {
  mode: 'date',
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: Date | Date[] | null): void;
  (e: 'onKeyupEnter'): void;
  (e: 'blur'): void;
}>();

const isDark = useDark();
const hasError = computed(() => !!props.error);
const formatStr = computed(() => props.mode === 'time' ? 'HH:mm' : 'dd/MM/yyyy');

const isTimeValue = (val: unknown): val is TimeValue => 
  !!val && typeof val === 'object' && !Array.isArray(val) && !(val instanceof Date) && 'hours' in val;

const toFullDate = (time: TimeValue): Date => {
  const base = props.modelValue instanceof Date ? props.modelValue : new Date();
  const d = new Date(base);
  d.setHours(time.hours, time.minutes, time.seconds ?? 0, 0);
  return d;
};

const dateValue = computed({
  get: () => {
    if (props.mode === 'time' && props.modelValue instanceof Date) {
      return {
        hours: props.modelValue.getHours(),
        minutes: props.modelValue.getMinutes(),
        seconds: props.modelValue.getSeconds(),
      };
    }
    return props.modelValue;
  },
  set: (val) => {
    if (!val) return emit('update:modelValue', null);

    if (isTimeValue(val)) {
      return emit('update:modelValue', toFullDate(val));
    }

    if (typeof val === 'string') {
      const parsed = dayjs(val);
      if (parsed.isValid()) emit('update:modelValue', parsed.toDate());
      return;
    }

    emit('update:modelValue', val as Date | Date[] | null);
  },
});
</script>

<template>
  <div class="flex flex-col gap-2 relative w-full" :class="{ 'has-error': hasError }">
    <label v-if="label" class="text-gray-700 dark:text-gray-100 font-semibold">{{ label }}</label>

    <VueDatePicker
      v-model="dateValue"
      :placeholder="placeholder || formatStr"
      :disabled="disabled"
      :dark="isDark"
      :locale="ptBR"
      :time-picker="mode === 'time'"
      :range="mode === 'range'"
      :is-24="true"
      :formats="{ input: formatStr }"
      :action-row="{ selectBtnLabel: 'Selecionar', cancelBtnLabel: 'Cancelar' }"
      teleport="body"
      hide-input-icon
      @blur="emit('blur')"
      @keydown.enter="emit('onKeyupEnter')"
    />

    <span v-if="error" class="text-[14px] text-danger">{{ error }}</span>
  </div>
</template>

<style>
/* Configurações globais do componente */
.dp__main {
  font-family: inherit;
  --dp-font-size: 14px;
  --dp-border-radius: 0.5rem; /* rounded-lg */
  --dp-input-padding: 1rem;
  --dp-input-height: 44px;
}

/* Variáveis para Tema Claro */
.dp__theme_light {
  --dp-background-color: var(--color-gray-100);
  --dp-text-color: var(--color-gray-700);
  --dp-hover-color: var(--color-gray-200);
  --dp-primary-color: var(--color-primary-default);
  --dp-primary-text-color: #ffffff;
  --dp-border-color: var(--color-gray-200);
  --dp-border-color-focus: var(--color-primary-default);
  --dp-menu-border-color: var(--color-gray-200);
}

/* Variáveis para Tema Escuro */
.dp__theme_dark {
  --dp-background-color: var(--color-gray-700);
  --dp-text-color: var(--color-gray-100);
  --dp-hover-color: var(--color-gray-600);
  --dp-primary-color: var(--color-primary-default);
  --dp-primary-text-color: #ffffff;
  --dp-border-color: var(--color-gray-600);
  --dp-border-color-focus: var(--color-primary-default);
  --dp-menu-border-color: var(--color-gray-600);
}

/* Forçar a cor primária no botão selecionar e datas ativas (caso a variável não tenha pego) */
.dp__action_select,
.dp__active_date,
.dp__overlay_action {
  background-color: var(--color-primary-default) !important;
  color: #ffffff !important;
}

/* Ajustes finos no input para bater 100% com o layout */
.dp__input {
  background-color: var(--dp-background-color) !important;
  color: var(--dp-text-color) !important;
  border: 1px solid var(--dp-border-color) !important;
  height: 44px !important;
  transition: all 0.2s ease-in-out;
  padding-inline-start: 1rem !important;
  font-size: 14px !important;
}

.dp__input:focus,
.dp__input_focus {
  box-shadow: 0 0 0 1px var(--dp-border-color-focus) !important;
  border-color: var(--dp-border-color-focus) !important;
  outline: none;
}

/* Estado de Erro */
.has-error .dp__input {
  border: 1px solid var(--color-danger) !important;
}

.has-error .dp__input:focus,
.has-error .dp__input_focus {
  box-shadow: 0 0 0 1px var(--color-danger) !important;
  border-color: var(--color-danger) !important;
}

/* Esconder o ícone de calendário extra */
.dp__input_icon {
  display: none !important;
}

/* Placeholder */
.dp__input::placeholder {
  color: #9ca3af; /* text-gray-400 */
  opacity: 1;
}

.dp__theme_dark .dp__input::placeholder {
  color: #d1d5db; /* text-gray-300 */
}

/* Estilização do Menu (Dropdown) */
.dp__menu {
  border: 1px solid var(--dp-menu-border-color) !important;
  background-color: var(--dp-background-color) !important;
}

.dp__theme_dark .dp__menu {
  background-color: var(--dp-background-color) !important;
}

/* Ajuste da seta do menu */
.dp__arrow_top, .dp__arrow_bottom {
  border: 1px solid var(--dp-menu-border-color);
  background-color: var(--dp-background-color) !important;
}

.dp__theme_dark .dp__arrow_top, 
.dp__theme_dark .dp__arrow_bottom {
  background-color: var(--dp-background-color) !important;
}
</style>