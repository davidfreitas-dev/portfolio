<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Projetos</h1>
      <button class="bg-blue-600 text-white px-4 py-2 rounded" @click="showModal = true">
        Novo Projeto
      </button>
    </div>

    <div class="mb-4 flex gap-4">
      <input v-model="search" placeholder="Buscar..." class="border p-2 rounded w-64" @input="loadProjects" />
      <select v-model="selectedStatus" class="border p-2 rounded" @change="loadProjects">
        <option value="">Todos</option>
        <option :value="1">Ativos</option>
        <option :value="0">Inativos</option>
      </select>
    </div>

    <div v-if="loading">Carregando projetos...</div>
    <div v-else>
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th>Título</th>
            <th>Status</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in projects" :key="p.id" class="border-t">
            <td>{{ p.title }}</td>
            <td>{{ p.is_active ? 'Ativo' : 'Inativo' }}</td>
            <td>
              <button @click="deleteItem(p.id)">Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal inline misturado na View -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center">
      <div class="bg-white p-6 rounded max-w-md w-full">
        <h2>Novo Projeto</h2>
        <input v-model="formTitle" placeholder="Título" class="border w-full my-2 p-2" />
        <textarea v-model="formDesc" placeholder="Descrição" class="border w-full my-2 p-2"></textarea>
        <div class="flex justify-end gap-2 mt-4">
          <button @click="showModal = false">Cancelar</button>
          <button @click="save">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const projects = ref([]);
const loading = ref(false);
const search = ref('');
const selectedStatus = ref('');
const showModal = ref(false);
const formTitle = ref('');
const formDesc = ref('');

async function loadProjects() {
  loading.value = true;
  const res = await axios.get('/admin/projects', {
    params: { search: search.value, is_active: selectedStatus.value || undefined }
  });
  projects.value = res.data.data.projects;
  loading.value = false;
}

async function save() {
  await axios.post('/admin/projects', { title: formTitle.value, description: formDesc.value });
  showModal.value = false;
  loadProjects();
}

async function deleteItem(id) {
  if (confirm('Deseja excluir?')) {
    await axios.delete('/admin/projects/' + id);
    loadProjects();
  }
}

onMounted(() => {
  loadProjects();
});
</script>
