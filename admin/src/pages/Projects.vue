<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from '../api/axios';
import MainContainer from '../components/shared/MainContainer.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import Wrapper from '../components/shared/Wrapper.vue';
import Button from '../components/shared/Button.vue';
import Pagination from '../components/shared/Pagination.vue';
import Loader from '../components/shared/Loader.vue';
import Toast from '../components/shared/Toast.vue';

const toastRef = ref(null);
const isLoading = ref(false);
const projects = ref([]);
const tableHead = reactive(['#', 'ID', 'Nome', 'Descrição', 'Data']);

const loadData = async () => {
  isLoading.value = true;

  try {
    const response = await axios.get('/projects');
    
    if (response) {
      projects.value = response.data;
    }
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast('error', 'Falha ao carregar projetos.');
  }

  isLoading.value = false;
};

onMounted(async () => {
  await loadData();
});
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Projetos" description="Adicione os projetos do seu portfolio.">
      <Button>
        <span class="material-icons">
          add
        </span>
        
        <span class="hidden md:block">
          Adicionar
        </span>
      </Button>
    </Breadcrumb>

    <Wrapper>
      <div class="text-center text-secondary my-10">
        <Loader v-if="isLoading" color="primary" />
        <span v-else>Nenhum projeto encontrado.</span>
      </div>

      <div v-if="!isLoading && projects.length" class="data-table relative overflow-x-auto my-3">
        <table class="w-full text-left text-gray-500">
          <thead class="border-b text-gray-500">
            <tr>
              <th
                v-for="(item, i) in tableHead"
                :key="i"
                scope="col"
                class="px-6 py-3"
              >
                {{ item }}
              </th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(project, i) in projects"
              :key="i"
              class="border-b hover:bg-gray-50"
            >
              <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                {{ i + 1 }}
              </th>

              <td class="px-6 py-4">
                #{{ project.idproject }}
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-5 min-w-[150px]">
                  <img :src="project.desimage" class="h-12 w-12 rounded-md">

                  <div class="hover:text-primary hover:underline cursor-pointer line-clamp-2">
                    {{ project.destitle }}
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-3 min-w-[300px]">
                  {{ project.desdescription }}
                </div>
              </td>

              <td class="px-6 py-4">
                {{ project.dtcreation }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        v-if="!isLoading && projects.length"
        ref="paginationRef"
        :total-pages="2"
        :total-items="10"
      />
    </Wrapper>

    <Toast ref="toastRef" />
  </MainContainer>
</template>
