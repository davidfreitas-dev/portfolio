<script setup>
import { ref, reactive } from 'vue';
import MainContainer from '../components/MainContainer.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Wrapper from '../components/Wrapper.vue';
import Button from '../components/Button.vue';
import Pagination from '../components/Pagination.vue';

const tableHead = reactive(['#', 'ID', 'Tecnologia']);

const techs = ref([
  {
    idtechnology: 1,
    desname: 'HTML',
    desimage: 'https://cdn-icons-png.flaticon.com/256/174/174854.png',
  },
  {
    idtechnology: 2,
    desname: 'CSS',
    desimage: 'https://cdn-icons-png.flaticon.com/256/732/732190.png'
  },
  {
    idtechnology: 3,
    desname: 'JavaScript',
    desimage: 'https://cdn-icons-png.flaticon.com/512/5968/5968292.png',
  }
]);
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
        ref="paginationRef"
        :total-pages="2"
        :total-items="10"
      />
    </Wrapper>
  </MainContainer>
</template>
