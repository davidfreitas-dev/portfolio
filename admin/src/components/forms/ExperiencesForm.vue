<script setup>
import { ref } from 'vue';
import axios from '../../api/axios';
import Button from '../shared/Button.vue';
import Input from '../shared/Input.vue';
import Textarea from '../shared/Textarea.vue';
import InputDate from '../shared/InputDate.vue';

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

  <InputDate label="Data de início" date-format="MM/YYYY" />

  <InputDate label="Data do fim" date-format="MM/YYYY" />

  <div class="flex flex-row-reverse">
    <Button class="mt-5">
      Salvar Dados
    </Button>
  </div>
</template>