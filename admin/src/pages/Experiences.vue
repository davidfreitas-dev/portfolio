<script setup>
import { ref, reactive } from 'vue';
import MainContainer from '../components/MainContainer.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Wrapper from '../components/Wrapper.vue';
import Button from '../components/Button.vue';
import Pagination from '../components/Pagination.vue';

const tableHead = reactive(['#', 'ID', 'Título', 'Descrição', 'Início', 'Fim']);

const experiences = ref([
  {
    idexperience: 1,
    destitle: 'Desenvolvedor Frontend',
    desdescription: 'Trabalhei em projetos de desenvolvimento de interfaces.',
    dtstart: '01/01/2020',
    dtend: '31/12/2020'
  },
  {
    idexperience: 2,
    destitle: 'Engenheiro de Software',
    desdescription: 'Desenvolvi sistemas backend escaláveis.',
    dtstart: '01/01/2021',
    dtend: '31/12/2021'
  },
  {
    idexperience: 3,
    destitle: 'Gerente de Projetos',
    desdescription: 'Gerenciei equipes de desenvolvimento e entreguei projetos dentro do prazo.',
    dtstart: '01/01/2022',
    dtend: '31/12/2022'
  }
]);
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Experiências" description="Adicione suas experiências profissionais ou colaborações em projetos.">
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
              v-for="(experience, i) in experiences"
              :key="i"
              class="border-b hover:bg-gray-50"
            >
              <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                {{ i + 1 }}
              </th>

              <td class="px-6 py-4">
                #{{ experience.idexperience }}
              </td>

              <td class="px-6 py-4">
                {{ experience.destitle }}
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-3 min-w-[250px]">
                  {{ experience.desdescription }}
                </div>
              </td>

              <td class="px-6 py-4">
                {{ experience.dtstart }}
              </td>

              <td class="px-6 py-4">
                {{ experience.dtend }}
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
