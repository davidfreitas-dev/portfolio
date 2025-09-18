<script setup lang="ts">
import { ref, computed, onMounted, watch, type VNode } from 'vue';
import { debounce } from 'vue-debounce';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import { useProjectsStore } from '@/stores/projectsStore';
import { useTechnologiesStore } from '@/stores/technologiesStore';
import Container from '@/components/Container.vue';
import Breadcrumb from '@/components/Breadcrumb.vue';
import Icon from '@/components/Icon.vue';
import Button from '@/components/Button.vue';
import InputSearch from '@/components/InputSearch.vue';
import Table from '@/components/Table.vue';
import Loader from '@/components/Loader.vue';
import Badge from '@/components/Badge.vue';
import ProjectForm, { type ProjectFormData } from '@/forms/ProjectForm.vue';
import Modal, { type ModalExpose } from '@/components/Modal.vue';
import Dialog from '@/components/Dialog.vue';
import Pagination from '@/components/Pagination.vue';

const projectsStore = useProjectsStore();
const { isLoading, withLoading } = useLoading();
const { showToast } = useToast();

const technologiesStore = useTechnologiesStore();
const technologiesOptions = computed(() => {
  return technologiesStore.technologies.map(tech => ({
    label: tech.name,
    value: tech.id!
  }));
});

const loadTechnologies = async () => {
  if (!technologiesStore.technologies.length) {
    await technologiesStore.fetchTechnologies();
  }
};

const page = ref(1);
const itemsPerPage = 5;
const search = ref('');
const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const loadProjects = async () => {
  await withLoading(() =>
    projectsStore.fetchProjects(page.value, itemsPerPage, normalizedSearch.value)
  );
};

const debouncedLoadProjects = debounce(loadProjects, '500ms');

watch(search, () => {
  page.value = 1;
  debouncedLoadProjects();
});

watch(page, () => {
  loadProjects();
});

onMounted(async () => {
  await loadTechnologies();
  await loadProjects();
});

const projects = computed(() => projectsStore.projects);
const tableHead = computed<(string | VNode)[]>(() => [
  'Imagem',
  'Título',
  'Descrição',
  'Status',
  'Ações',
]);

const isEditing = ref(false);
const projectModal = ref<ModalExpose | null>(null);
const projectBeingEdited = ref<ProjectFormData | null>(null);

const openCreateModal = () => {
  isEditing.value = false;
  projectBeingEdited.value = null;
  projectModal.value?.openModal();
};

const openEditModal = (proj: ProjectFormData) => {
  isEditing.value = true;
  projectBeingEdited.value = { ...proj };
  projectModal.value?.openModal();
};

const handleSubmit = async (payload: ProjectFormData) => {
  try {
    await projectsStore.saveProject({
      id: isEditing.value ? projectBeingEdited.value?.id : undefined,
      title: payload.title,
      description: payload.description,
      link: payload.link,
      image: payload.image ?? undefined,
      is_active: payload.is_active,
      technologies: payload.technologies,
    });

    showToast(
      'success',
      isEditing.value
        ? 'Projeto atualizado com sucesso!'
        : 'Projeto adicionado com sucesso!'
    );

    projectModal.value?.closeModal();
    await loadProjects();
  } catch (err) {
    console.error(err);
  }
};

const handleModalClose = () => {
  isEditing.value = false;
  projectBeingEdited.value = null;
};

const dialogRef = ref<InstanceType<typeof Dialog> | null>(null);
const projectToDelete = ref<number | null>(null);

const handleDeleteProject = (id: number) => {
  projectToDelete.value = id;
  dialogRef.value?.openModal();
};

const deleteProject = async () => {
  if (!projectToDelete.value) return;
  await projectsStore.deleteProject(projectToDelete.value);
  showToast('success', 'Projeto deletado com sucesso!');
  await loadProjects();
};

const apiUrl = import.meta.env.VITE_API_URL;
const getProjectImage = (image: string) => `${apiUrl}/images/projects/${image}`;
</script>

<template>
  <Container>
    <div class="header flex justify-between items-center flex-wrap gap-4">
      <Breadcrumb title="Projetos" description="Gerencie seus projetos aqui." />
      <div class="flex gap-2 ml-auto">
        <Button class="h-fit" @click="openCreateModal">
          <Icon name="add" class="md:mr-2" />
          <span class="hidden md:block">Novo Projeto</span>
        </Button>
      </div>
    </div>

    <div class="relative rounded-3xl border border-neutral dark:border-neutral-dark my-8">
      <div class="filters grid grid-cols-1 md:grid-cols-2 gap-4 w-full border-b border-neutral dark:border-neutral-dark p-5">
        <InputSearch
          v-model="search"
          label="Buscar por título"
          floating-label
        />
      </div>

      <Loader
        v-if="isLoading"
        color="primary"
        class="w-4 h-4 mx-auto my-10"
      />

      <div class="rounded-2xl overflow-auto">
        <Table
          v-if="!isLoading && projects.length"
          :headers="tableHead"
          :items="projects"
        >
          <template #row="{ item: proj }">
            <td class="px-6 py-4 w-[15%] max-w-[150px]">
              <img
                v-if="proj.image && typeof proj.image === 'string'"
                :src="getProjectImage(proj.image)"
                alt="Project cover"
                class="w-14 h-14 object-cover rounded-lg"
              >
            </td>
            <td class="px-6 py-4 truncate text-font dark:text-white">
              {{ proj.title }}
            </td>
            <td class="px-6 py-4 truncate text-secondary dark:text-gray-300 max-w-[250px]">
              {{ proj.description }}
            </td>
            <td class="px-6 py-4">
              <Badge :label="proj.is_active ? 'Ativo' : 'Inativo'" :color="proj.is_active ? 'success' : 'danger'" />
            </td>
            <td class="px-6 py-4 w-[5%] min-w-[50px]">
              <div class="flex item-center gap-3">
                <button
                  class="p-2 h-10 w-10 bg-primary-accent dark:bg-primary-accent-dark text-primary dark:text-primary-dark rounded-full cursor-pointer"
                  @click="openEditModal(proj)"
                >
                  <Icon name="edit" />
                </button>
                <button
                  class="p-2 h-10 w-10 bg-danger-accent dark:bg-danger-accent-dark text-danger dark:text-danger-dark rounded-full cursor-pointer"
                  @click="handleDeleteProject(proj.id!)"
                >
                  <Icon name="delete" />
                </button>
              </div>
            </td>
          </template>
        </Table>
      </div>

      <div
        v-if="!isLoading && !projects.length"
        class="text-secondary dark:text-gray-400 text-center my-10"
      >
        Nenhum projeto encontrado.
      </div>
    </div>

    <Pagination
      v-if="!isLoading && projects.length"
      v-model="page"
      :total-items="projectsStore.totalItems"
      :items-per-page="itemsPerPage"
    />

    <Modal
      ref="projectModal"
      :title="isEditing ? 'Editar Projeto' : 'Novo Projeto'"
      @on-modal-close="handleModalClose"
    >
      <ProjectForm
        :mode="isEditing ? 'edit' : 'create'"
        :initial-data="projectBeingEdited"
        :technologies-options="technologiesOptions"
        @submit="handleSubmit"
        @cancel="projectModal?.closeModal()"
      />
    </Modal>

    <Dialog
      ref="dialogRef"
      header="Tem certeza que deseja deletar este projeto?"
      message="Se confirmada essa ação não poderá ser desfeita."
      @confirm-action="deleteProject"
    />
  </Container>
</template>
