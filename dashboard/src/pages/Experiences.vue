<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { debounce } from 'vue-debounce';
import axios from '../api/axios';
import MainContainer from '../components/shared/MainContainer.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import Wrapper from '../components/shared/Wrapper.vue';
import InputSearch from '../components/shared/InputSearch.vue';
import Pagination from '../components/shared/Pagination.vue';
import Button from '../components/shared/Button.vue';
import Loader from '../components/shared/Loader.vue';
import Toast from '../components/shared/Toast.vue';
import Dialog from '../components/shared/Dialog.vue';
import Modal from '../components/shared/Modal.vue';
import ExperiencesForm from '../components/forms/ExperiencesForm.vue';

const tableHead = reactive(['#', 'ID', 'Título', 'Descrição', 'Início', 'Fim', 'Ações']);

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
  
  let endpoint = `/experiences/page/${page.value}`;

  if (search.value) {
    endpoint = `/experiences/search/${search.value}/${page.value}`;
  }

  try {
    const response = await axios.get(endpoint);
    data.value = response.data ?? null;
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast('error', 'Falha ao carregar experiências.');
  }

  isLoading.value = false;
};

onMounted(async () => {
  await loadData();
});

const deleteExperience = async () => {
  const technologyId = selectedExperience.value.idexperience;
  
  try {
    await axios.delete(`/experiences/delete/${technologyId}`);
    await loadData();
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao deletar experiência');
  }
};

const dialogRef = ref(null);

const handleDeleteExperience = async (experience) => {
  selectedExperience.value = experience;
  dialogRef.value?.openModal();
};

const modalRef = ref(null);

const showModal = () => {
  modalRef.value?.setOpen();
};

const closeModal = () => {
  modalRef.value?.closeModal();
};

const selectedExperience = ref(null);

const handleExperience = (experience) => {
  selectedExperience.value = experience;
  showModal();
};
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Experiências" description="Adicione suas experiências profissionais ou colaborações em projetos." />

    <Wrapper>
      <div class="flex justify-between items-center w-full my-5">
        <InputSearch v-model="search" placeholder="Buscar Experiência" />

        <Button @click="handleExperience">
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
        <span v-if="!isLoading && (!data || !data.experiences.length)">
          Nenhuma experiência encontrada.
        </span>
      </div>

      <div v-if="!isLoading && data && data.experiences.length" class="data-table relative overflow-x-auto my-3">
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
              v-for="(experience, i) in data.experiences"
              :key="i"
              class="border-b hover:bg-gray-50"
            >
              <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                {{ i + 1 }}
              </th>

              <td class="px-6 py-4">
                #{{ experience.idexperience }}
              </td>

              <td class="px-6 py-4 hover:text-primary hover:underline cursor-pointer truncate" @click="handleExperience(experience)">
                {{ experience.destitle }}
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-3 w-[250px]">
                  <div class="truncate">
                    {{ experience.desdescription }}
                  </div>
                </div>
              </td>

              <td class="px-6 py-4 truncate">
                {{ $filters.formatDateMonthYear(experience.dtstart) }}
              </td>

              <td class="px-6 py-4 truncate">
                {{ $filters.formatDateMonthYear(experience.dtend) }}
              </td>

              <td class="px-6 py-4">
                <div class="flex gap-3">
                  <Button size="small" @click="handleExperience(experience)">
                    <span class="material-icons">
                      edit
                    </span>
                  </Button>

                  <Button
                    size="small"
                    color="danger"
                    @click="handleDeleteExperience(experience)"
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
      header="Deseja realmente excluir esta experiência?"
      message="Ao clicar em confirmar as informações serão excluidas permanentemente."
      @confirm-action="deleteExperience"
    />

    <Modal
      ref="modalRef"
      title="Experiências"
      @on-modal-close="loadData"
    >
      <ExperiencesForm :experience="selectedExperience" @on-close-modal="closeModal" />
    </Modal>

    <Toast ref="toastRef" />
  </MainContainer>
</template>
