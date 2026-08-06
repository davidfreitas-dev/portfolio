<script setup lang="ts">
import { ref } from 'vue';
import Button from '@/components/Button.vue';
import Input from '@/components/Input.vue';
import InputDate from '@/components/InputDate.vue';
import Textarea from '@/components/Textarea.vue';
import Switch from '@/components/Switch.vue';
import Checkbox from '@/components/Checkbox.vue';
import Select from '@/components/Select.vue';
import Icon from '@/components/Icon.vue';
import Badge from '@/components/Badge.vue';
import Loader from '@/components/Loader.vue';
import Container from '@/components/Container.vue';
import InputSearch from '@/components/InputSearch.vue';

const inputValue = ref('');
const inputFilled = ref('Texto preenchido');
const dateValue = ref<Date | null>(null);
const selectValue = ref(null);
const selectOptions = [
  { label: 'Opção 1', value: 1 },
  { label: 'Opção 2', value: 2 },
  { label: 'Opção 3', value: 3 },
];

const switchActive = ref(true);
const switchInactive = ref(false);

const checkActive = ref(true);
const checkInactive = ref(false);

const colorGroups = [
  {
    title: 'Primary',
    colors: [
      { name: 'Primary Dark', class: 'bg-primary-dark' },
      { name: 'Primary Default', class: 'bg-primary-default' },
      { name: 'Primary Light', class: 'bg-primary-light' },
      { name: 'Primary Bg', class: 'bg-primary-bg', textClass: 'text-gray-700' },
    ]
  },
  {
    title: 'Grays / Neutrals',
    colors: [
      { name: 'Grey 900', class: 'bg-gray-900' },
      { name: 'Grey 800', class: 'bg-gray-800' },
      { name: 'Grey 700', class: 'bg-gray-700' },
      { name: 'Grey 600', class: 'bg-gray-600' },
      { name: 'Grey 500', class: 'bg-gray-500' },
      { name: 'Grey 400', class: 'bg-gray-400' },
      { name: 'Grey 300', class: 'bg-gray-300', textClass: 'text-gray-700' },
      { name: 'Grey 200', class: 'bg-gray-200', textClass: 'text-gray-700' },
      { name: 'Grey 100', class: 'bg-gray-100', textClass: 'text-gray-700' },
      { name: 'White', class: 'bg-white border border-gray-200 dark:border-gray-700', textClass: 'text-gray-700' },
    ]
  },
  {
    title: 'Feedback (Status)',
    colors: [
      { name: 'Success', class: 'bg-success' },
      { name: 'Warning', class: 'bg-warning', textClass: 'text-gray-700' },
      { name: 'Danger', class: 'bg-danger' },
    ]
  }
];
</script>

<template>
  <Container class="pb-20">
    <div class="mb-10">
      <h1 class="text-3xl font-bold text-gray-700 dark:text-gray-100 mb-2">
        Design System
      </h1>
      <p class="text-gray-500 dark:text-gray-300">
        Catálogo de componentes e estilos da aplicação.
      </p>
    </div>

    <!-- Cores -->
    <section class="mb-12">
      <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100 mb-6 border-b pb-2 border-gray-200 dark:border-gray-600">
        Cores
      </h2>
      <div
        v-for="group in colorGroups"
        :key="group.title"
        class="mb-8"
      >
        <h3 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-100">
          {{ group.title }}
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
          <div
            v-for="color in group.colors"
            :key="color.name"
            class="flex flex-col"
          >
            <div :class="['h-20 w-full rounded-lg shadow-sm flex items-end p-2', color.class]">
              <span :class="['text-xs font-semibold', color.textClass || 'text-white']">{{ color.name }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Tipografia -->
    <section class="mb-12">
      <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100 mb-6 border-b pb-2 border-gray-200 dark:border-gray-600">
        Tipografia (Inter)
      </h2>
      <div class="space-y-6 text-gray-700 dark:text-gray-100">
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Heading 1 / 32px / Bold</span><h1 class="text-[32px] font-bold">
            Facilisis ornare hac
          </h1>
        </div>
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Heading 2 / 28px / Semi bold</span><h2 class="text-[28px] font-semibold">
            Tellus metus hendrerit.
          </h2>
        </div>
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Heading 3 / 24px / Semi bold</span><h3 class="text-[24px] font-semibold">
            Tellus metus hendrerit.
          </h3>
        </div>
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Heading 4 / 20px / Bold</span><h4 class="text-[20px] font-bold">
            Tellus metus hendrerit.
          </h4>
        </div>
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Subtitle 1 / 18px / Semi bold</span><div class="text-[18px] font-semibold">
            Tellus metus hendrerit.
          </div>
        </div>
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Subtitle 2 / 14px / Medium</span><div class="text-[14px] font-medium">
            Tellus metus hendrerit.
          </div>
        </div>
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Body 1 / 18px / Regular</span><p class="text-[18px] font-normal">
            Tellus metus hendrerit.
          </p>
        </div>
        <div>
          <span class="text-xs text-gray-400 mb-1 block">Body 2 / 16px / Regular</span><p class="text-[16px] font-normal">
            Tellus metus hendrerit.
          </p>
        </div>
      </div>
    </section>

    <!-- Buttons -->
    <section class="mb-12">
      <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100 mb-6 border-b pb-2 border-gray-200 dark:border-gray-600">
        Botões
      </h2>
 
      <div class="space-y-8">
        <!-- Variantes -->
        <div>
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">
            Variantes (Default, Hover, Disabled, Loading)
          </h3>
          <div class="flex flex-wrap items-center gap-6">
            <div class="flex flex-col gap-3">
              <Button variant="fill">
                Fill Default
              </Button>
              <Button variant="fill" disabled>
                Fill Disabled
              </Button>
              <Button variant="fill" is-loading>
                Loading
              </Button>
            </div>
 
            <div class="flex flex-col gap-3">
              <Button variant="outline">
                Outline Default
              </Button>
              <Button variant="outline" disabled>
                Outline Disabled
              </Button>
              <Button variant="outline" is-loading>
                Loading
              </Button>
            </div>
 
            <div class="flex flex-col gap-3">
              <Button variant="link">
                Link Default
              </Button>
              <Button variant="link" disabled>
                Link Disabled
              </Button>
              <Button variant="link" is-loading>
                Loading
              </Button>
            </div>
          </div>
        </div>
 
        <!-- Tamanhos -->
        <div>
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">
            Tamanhos (Large, Medium, Small)
          </h3>
          <div class="flex flex-wrap items-end gap-6">
            <Button size="large">
              Button Large
            </Button>
            <Button size="medium">
              Button Medium
            </Button>
            <Button size="small">
              Button Small
            </Button>
          </div>
        </div>
 
        <!-- Com ícones -->
        <div>
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100 mb-4">
            Com Ícones
          </h3>
          <div class="flex flex-wrap items-center gap-6">
            <Button>
              <template #left-icon>
                <Icon name="add" class="w-5 h-5" />
              </template>
              Icon Left
            </Button>
 
            <Button variant="outline">
              Icon Right
              <template #right-icon>
                <Icon name="chevron_left" class="w-5 h-5 rotate-180" />
              </template>
            </Button>
          </div>
        </div>
      </div>
    </section>

    <!-- Formulários -->
    <section class="mb-12">
      <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100 mb-6 border-b pb-2 border-gray-200 dark:border-gray-600">
        Formulários (Inputs, Selects, Textarea)
      </h2>
 
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="space-y-4">
          <Input
            v-model="inputValue"
            label="Input Default"
            placeholder="Placeholder..."
          />
          <Input v-model="inputFilled" label="Input Preenchido" />
          <Input
            v-model="inputValue"
            label="Input Disabled"
            placeholder="Desativado"
            disabled
          />
          <Input
            v-model="inputValue"
            label="Input com Erro"
            placeholder="Digite..."
            error="Mensagem de erro obrigatória"
          />
          <InputSearch v-model="inputValue" placeholder="Buscar..." />
        </div>
 
        <div class="space-y-4">
          <Select
            v-model="selectValue"
            :options="selectOptions"
            label="Select Default"
          />
          <Select
            v-model="selectValue"
            :options="selectOptions"
            label="Select com Erro"
            error="Seleção obrigatória"
          />
          <InputDate
            v-model="dateValue"
            label="Input Date Default"
          />
          <InputDate
            v-model="dateValue"
            label="Input Date com Erro"
            error="Data inválida"
          />
        </div>
 
        <div class="space-y-4">
          <Textarea
            v-model="inputValue"
            label="Textarea Default"
            placeholder="Escreva algo..."
          />
          <Textarea
            v-model="inputValue"
            label="Textarea com Erro"
            error="Campo obrigatório"
          />
        </div>
      </div>
    </section>

    <!-- Controles -->
    <section class="mb-12">
      <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100 mb-6 border-b pb-2 border-gray-200 dark:border-gray-600">
        Controles (Switch & Checkbox)
      </h2>
 
      <div class="flex gap-16">
        <div class="space-y-4">
          <h3 class="font-semibold text-gray-700 dark:text-gray-100">
            Switch
          </h3>
          <div class="flex items-center gap-4">
            <Switch v-model="switchActive" /> <span class="text-gray-700 dark:text-gray-100">Ativo</span>
          </div>
          <div class="flex items-center gap-4">
            <Switch v-model="switchInactive" /> <span class="text-gray-700 dark:text-gray-100">Inativo</span>
          </div>
        </div>
 
        <div class="space-y-4">
          <h3 class="font-semibold text-gray-700 dark:text-gray-100">
            Checkbox
          </h3>
          <Checkbox v-model="checkActive" label="Ativo" />
          <Checkbox v-model="checkInactive" label="Inativo" />
        </div>
      </div>
    </section>
 
    <!-- Outros componentes -->
    <section class="mb-12">
      <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100 mb-6 border-b pb-2 border-gray-200 dark:border-gray-600">
        Outros (Badges, Ícones, Loaders)
      </h2>
 
      <div class="space-y-8">
        <div>
          <h3 class="font-semibold text-gray-700 dark:text-gray-100 mb-4">
            Badges
          </h3>
          <div class="flex gap-4">
            <Badge label="Success" color="success" />
            <Badge label="Warning" color="warning" />
            <Badge label="Danger" color="danger" />
          </div>
        </div>
 
        <div>
          <h3 class="font-semibold text-gray-700 dark:text-gray-100 mb-4">
            Loader
          </h3>
          <div class="flex flex-wrap items-center gap-6">
            <div class="p-4 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
              <Loader color="primary" />
            </div>
            <div class="p-4 rounded-lg bg-primary-default flex items-center justify-center">
              <Loader color="white" />
            </div>
          </div>
        </div>

        <div>
          <h3 class="font-semibold text-gray-700 dark:text-gray-100 mb-4">
            Ícones
          </h3>
          <div class="flex flex-wrap gap-4 text-gray-700 dark:text-gray-100">
            <Icon name="home" class="w-6 h-6" />
            <Icon name="settings" class="w-6 h-6" />
            <Icon name="edit" class="w-6 h-6" />
            <Icon name="delete" class="w-6 h-6" />
            <Icon name="visibility" class="w-6 h-6" />
            <Icon name="add" class="w-6 h-6" />
            <Icon name="check" class="w-6 h-6" />
            <Icon name="close" class="w-6 h-6" />
          </div>
        </div>
      </div>
    </section>
  </Container>
</template>
