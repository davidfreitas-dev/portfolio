<script setup>
import { ref } from 'vue';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import Textarea from '../shared/Textarea.vue';
import Select from '../shared/Select.vue';

const months = [
  'Janeiro', 
  'Fevereiro', 
  'Março', 
  'Abril', 
  'Maio', 
  'Junho', 
  'Julho', 
  'Agosto', 
  'Setembro', 
  'Outubro', 
  'Novembro', 
  'Dezembro'
];
</script>

<template>
  <Input
    label="Título"
    placeholder="Título da experiência"
  />

  <Textarea 
    label="Descrição" 
    placeholder="Descrição da experiência" 
  />

  <Select label="Início" :options="months" />

  <Select label="Fim" :options="months" />

  <div class="flex flex-row-reverse">
    <Button class="mt-5">
      Salvar Dados
    </Button>
  </div>
</template>