<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from '../api/axios';
import MainContainer from '../components/shared/MainContainer.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import Wrapper from '../components/shared/Wrapper.vue';
import Pagination from '../components/shared/Pagination.vue';
import Button from '../components/shared/Button.vue';
import Loader from '../components/shared/Loader.vue';
import Toast from '../components/shared/Toast.vue';
import Modal from '../components/shared/Modal.vue';
import ExperiencesForm from '../components/forms/ExperiencesForm.vue';

const tableHead = reactive(['#', 'ID', 'Título', 'Descrição', 'Início', 'Fim']);

const page = ref(1);
const toastRef = ref(null);
const paginationRef = ref(null);
const isLoading = ref(false);

const changePage = (currentPage) => {
  page.value = currentPage;
  loadData();
};

const data = ref(null);

const loadData = async () => {
  isLoading.value = true;

  try {
    const response = await axios.get(`/experiences/page/${page.value}`);
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

const modalRef = ref(null);

const showModal = () => {
  modalRef.value?.setOpen();
};

const closeModal = () => {
  modalRef.value?.closeModal();
};
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Experiências" description="Adicione suas experiências profissionais ou colaborações em projetos.">
      <Button @click="showModal">
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

              <td class="px-6 py-4">
                {{ experience.destitle }}
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-3 min-w-[275px]">
                  {{ experience.desdescription }}
                </div>
              </td>

              <td class="px-6 py-4">
                {{ $filters.formatDate(experience.dtstart) }}
              </td>

              <td class="px-6 py-4">
                {{ $filters.formatDate(experience.dtend) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination
        v-if="!isLoading && data"
        ref="paginationRef"
        :total-pages="data.pages"
        :total-items="data.total"
        @on-page-change="changePage"
      />
    </Wrapper>

    <Modal
      ref="modalRef"
      title="Experiências"
      @on-modal-close="loadData"
    >
      <ExperiencesForm @on-close-modal="closeModal" />
    </Modal>

    <Toast ref="toastRef" />
  </MainContainer>
</template>
