<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { debounce } from 'vue-debounce';
import axios from '../api/axios';
import MainContainer from '../components/shared/MainContainer.vue';
import Breadcrumb from '../components/shared/Breadcrumb.vue';
import Wrapper from '../components/shared/Wrapper.vue';
import InputSearch from '../components/shared/InputSearch.vue';
import Button from '../components/shared/Button.vue';
import Dialog from '../components/shared/Dialog.vue';
import Pagination from '../components/shared/Pagination.vue';
import Loader from '../components/shared/Loader.vue';
import Toast from '../components/shared/Toast.vue';
import Modal from '../components/shared/Modal.vue';
import ProjectsForm from '../components/forms/ProjectsForm.vue';

const tableHead = reactive(['#', 'ID', 'Status', 'Título', 'Descrição', 'Atualizado em', 'Ações']);

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

  let endpoint = `/projects/page/${page.value}`;

  if (search.value) {
    endpoint = `/projects/search/${search.value}/${page.value}`;
  }

  try {
    const response = await axios.get(endpoint);
    data.value = response.data ?? null;
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast('error', 'Falha ao carregar projetos.');
  }

  isLoading.value = false;
};

onMounted(async () => {
  await loadData();
});

const deleteProject = async () => {
  const projectId = selectedProject.value.idproject;
  
  try {
    await axios.delete(`/projects/delete/${projectId}`);
    loadData();
  } catch (error) {
    console.log(error);
    toastRef.value?.showToast(error.data?.status, 'Falha ao deletar projeto');
  }
};

const dialogRef = ref(null);

const handleDeleteProject = async (project) => {
  selectedProject.value = project;
  dialogRef.value?.openModal();
};

const modalRef = ref(null);

const showModal = () => {
  modalRef.value?.setOpen();
};

const closeModal = () => {
  modalRef.value?.closeModal();
};

const selectedProject = ref(null);

const handleProject = (project) => {
  selectedProject.value = project;
  showModal();
};
</script>

<template>
  <MainContainer>
    <Breadcrumb title="Projetos" description="Adicione os projetos do seu portfolio." />

    <Wrapper>
      <div class="flex justify-between items-center w-full my-5">
        <InputSearch v-model="search" placeholder="Buscar Projeto" />

        <Button @click="handleProject">
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
        <span v-if="!isLoading && (!data || !data.projects.length)">
          Nenhum projeto encontrado.
        </span>
      </div>

      <div v-if="!isLoading && data && data.projects.length" class="data-table relative overflow-x-auto my-3">
        <table class="w-full text-left text-gray-500">
          <thead class="border-b text-gray-500">
            <tr>
              <th
                v-for="(item, i) in tableHead"
                :key="i"
                scope="col"
                class="px-6 py-3 truncate"
              >
                {{ item }}
              </th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="(project, i) in data.projects"
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
                <span v-if="project.inactive === 1" class="material-icons text-primary">
                  check_circle
                </span>

                <span v-else class="material-icons text-red-500">
                  cancel
                </span>
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-5 min-w-[150px]">
                  <img
                    v-if="project.desimage"
                    :src="project.desimage"
                    class="h-12 w-12 rounded-md"
                  >

                  <div class="hover:text-primary hover:underline cursor-pointer truncate" @click="handleProject(project)">
                    {{ project.destitle }}
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                <div class="flex items-center gap-3 w-[250px]">
                  <div class="truncate">
                    {{ project.desdescription }}
                  </div>
                </div>
              </td>

              <td class="px-6 py-4">
                {{ $filters.formatDate(project.dtupdate) }}
              </td>

              <td class="px-6 py-4">
                <div class="flex gap-3">
                  <Button size="small" @click="handleProject(project)">
                    <span class="material-icons">
                      edit
                    </span>
                  </Button>

                  <Button
                    size="small"
                    color="danger"
                    @click="handleDeleteProject(project)"
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
      header="Deseja realmente excluir este projeto?"
      message="Ao clicar em confirmar as informações serão excluidas permanentemente."
      @confirm-action="deleteProject"
    />

    <Modal
      ref="modalRef"
      title="Projetos"
      @on-modal-close="loadData"
    >
      <ProjectsForm :project="selectedProject" @on-close-modal="closeModal" />
    </Modal>

    <Toast ref="toastRef" />
  </MainContainer>
</template>
