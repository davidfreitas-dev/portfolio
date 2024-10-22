<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { debounce } from 'vue-debounce';
import axios from '../api/axios';
import MainContainer from '../components/shared/MainContainer.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import Wrapper from '../components/shared/Wrapper.vue';
import InputSearch from '../components/shared/InputSearch.vue';
import Button from '../components/shared/Button.vue';
import Pagination from '../components/shared/Pagination.vue';
import Loader from '../components/shared/Loader.vue';
import Toast from '../components/shared/Toast.vue';
import Dialog from '../components/shared/Dialog.vue';
import Modal from '../components/shared/Modal.vue';
import TechnologiesForm from '../components/forms/TechnologiesForm.vue';

const emit = defineEmits(['onSearch']);

const tableHead = reactive(['#', 'ID', 'Tecnologia', 'Ações']);

const page = ref(1);
const search = ref('');
const toastRef = ref(null);
const paginationRef = ref(null);
const isLoading = ref(false);

const handleDebounce = debounce((search) => loadData(), '500ms');

watch(search, (newSearch) => {
  handleDebounce(newSearch);
});

const changePage = (currentPage) => {
  page.value = currentPage;
  loadData();
};

const data = ref(null);

const loadData = async () => {
  isLoading.value = true;

  let endpoint = `/technologies/page/${page.value}`;

  if (search.value) {
    endpoint = `/technologies/search/${search.value}/${page.value}`;
  }

  try {
    const response = await axios.get(endpoint);
    data.value = response.data ?? null;
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast('error', 'Falha ao carregar tecnologias.');
  }

  isLoading.value = false;
};

onMounted(async () => {
  await loadData();
});

const deleteTech = async () => {
  const technologyId = selectedTechnology.value.idtechnology;
  
  try {
    await axios.delete(`/technologies/delete/${technologyId}`);
    await loadData();
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao deletar tecnologia');
  }
};

const dialogRef = ref(null);

const handleDeleteTech = async (technology) => {
  selectedTechnology.value = technology;
  dialogRef.value?.openModal();
};

const modalRef = ref(null);

const showModal = () => {
  modalRef.value?.setOpen();
};

const closeModal = () => {
  modalRef.value?.closeModal();
};

const selectedTechnology = ref(null);

const handleTechnology = (technology) => {
  selectedTechnology.value = technology;
  showModal();
};
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Tecnologias" description="Adicione as tecnologias usadas em seus projetos." />

    <Wrapper>
      <div class="flex justify-between items-center w-full my-5">
        <InputSearch v-model="search" placeholder="Buscar Tecnologia" />

        <Button @click="handleTechnology">
          <span class="material-icons">
            add
          </span>
  
          <span class="hidden md:block">
            Adicionar
          </span>
        </Button>
      </div>

      <div class="flex justify-center items-center w-full text-secondary my-10">
        <Loader v-if="isLoading" color="primary" />
        <span v-if="!isLoading && (!data || !data.technologies.length)">
          Nenhuma tecnologia encontrada.
        </span>
      </div>

      <div v-if="!isLoading && data && data.technologies.length" class="data-table relative overflow-x-auto my-3">
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
              v-for="(tech, i) in data.technologies"
              :key="i"
              class="border-b hover:bg-gray-50"
            >
              <th scope="row" class="w-[10%] px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                {{ i + 1 }}
              </th>

              <td class="w-[10%] px-6 py-4">
                #{{ tech.idtechnology }}
              </td>

              <td class="w-[65%] px-6 py-4">
                <div class="flex items-center gap-5 min-w-[150px]">
                  <img
                    v-if="tech.desimage"
                    :src="tech.desimage"
                    class="h-12 w-12 rounded-md"
                  >

                  <div class="hover:text-primary hover:underline cursor-pointer line-clamp-2" @click="handleTechnology(tech)">
                    {{ tech.desname }}
                  </div>
                </div>
              </td>

              <td class="w-[15%] px-6 py-4">
                <div class="flex gap-3">
                  <Button size="small" @click="handleTechnology(tech)">
                    <span class="material-icons">
                      edit
                    </span>
                  </Button>

                  <Button
                    size="small"
                    color="danger"
                    @click="handleDeleteTech(tech)"
                  >
                    <span class="material-icons">
                      delete
                    </span>
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        ref="paginationRef"
        :total-pages="data?.pages"
        :total-items="data?.total"
        @on-page-change="changePage"
      />
    </Wrapper>
    
    <Dialog
      ref="dialogRef"
      header="Deseja realmente excluir esta tecnologia?"
      message="Ao clicar em confirmar as informações serão excluidas permanentemente."
      @confirm-action="deleteTech"
    />

    <Modal
      ref="modalRef"
      title="Tecnologias"
      @on-modal-close="loadData"
    >
      <TechnologiesForm :technology="selectedTechnology" @on-close-modal="closeModal" />
    </Modal>

    <Toast ref="toastRef" />
  </MainContainer>
</template>
