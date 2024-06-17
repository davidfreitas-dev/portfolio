<script setup>
import { ref, reactive } from 'vue';
import MainContainer from '../components/MainContainer.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Wrapper from '../components/Wrapper.vue';
import Button from '../components/Button.vue';
import Pagination from '../components/Pagination.vue';

const tableHead = reactive(['#', 'ID', 'Nome', 'Descrição', 'Data']);

const projects = ref([
  {
    idproject: 1,
    destitle: 'Blog',
    desdescription: 'Desenvolvimento de interfaces para um blog.',
    desimage: '',
    dtcreation: '31/12/2020'
  },
  {
    idproject: 2,
    destitle: 'E-commerce',
    desdescription: 'Desenvolvimento de sistemas backend escaláveis para uma plataforma de e-commerce.',
    desimage: '',
    dtcreation: '31/12/2021'
  },
  {
    idproject: 3,
    destitle: 'News App',
    desdescription: 'Gerenciamento de equipes de desenvolvimento e entrega de um aplicativo de notícias dentro do prazo.',
    desimage: '',
    dtcreation: '31/12/2022'
  }
]);
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
      <div class="data-table relative overflow-x-auto my-3">
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
        ref="paginationRef"
        :total-pages="2"
        :total-items="10"
      />
    </Wrapper>
  </MainContainer>
</template>
