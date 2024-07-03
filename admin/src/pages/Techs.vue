<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from '../api/axios';
import MainContainer from '../components/MainContainer.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Wrapper from '../components/Wrapper.vue';
import Button from '../components/Button.vue';
import Pagination from '../components/Pagination.vue';
import Loader from '../components/Loader.vue';
import Toast from '../components/Toast.vue';

const toastRef = ref(null);
const isLoading = ref(false);
const techs = ref([]);
const tableHead = reactive(['#', 'ID', 'Tecnologia']);

const loadData = async () => {
  isLoading.value = true;

  try {
    const response = await axios.get('/technologies');
    
    if (response) {
      techs.value = response.data;
    }
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast('error', 'Falha ao carregar tecnologias.');
  }

  isLoading.value = false;
};

onMounted(async () => {
  await loadData();
});
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Tecnologias" description="Adicione as tecnologias usadas em seus projetos.">
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
        <span v-else>Nenhuma tecnologia encontrada.</span>
      </div>

      <div v-if="!isLoading && techs.length" class="data-table relative overflow-x-auto my-3">
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
              v-for="(tech, i) in techs"
              :key="i"
              class="border-b hover:bg-gray-50"
            >
              <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                {{ i + 1 }}
              </th>

              <td class="px-6 py-4">
                #{{ tech.idtechnology }}
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-5 min-w-[150px]">
                  <img :src="tech.desimage" class="h-12 w-12 rounded-md">

                  <div class="hover:text-primary hover:underline cursor-pointer line-clamp-2">
                    {{ tech.desname }}
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        v-if="!isLoading && techs.length"
        ref="paginationRef"
        :total-pages="2"
        :total-items="10"
      />
    </Wrapper>

    <Toast ref="toastRef" />
  </MainContainer>
</template>
