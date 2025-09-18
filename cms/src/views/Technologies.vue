<script setup lang="ts">
import { ref, computed, onMounted, type VNode, watch } from 'vue';
import { debounce } from 'vue-debounce';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import { useTechnologiesStore } from '@/stores/technologiesStore';
import Container from '@/components/Container.vue';
import Breadcrumb from '@/components/Breadcrumb.vue';
import Icon from '@/components/Icon.vue';
import Button from '@/components/Button.vue';
import InputSearch from '@/components/InputSearch.vue';
import Table from '@/components/Table.vue';
import Loader from '@/components/Loader.vue';
import TechnologyForm, { type TechnologyFormData } from '@/forms/TechnologyForm.vue';
import Modal, { type ModalExpose } from '@/components/Modal.vue';
import Dialog from '@/components/Dialog.vue';
import Pagination from '@/components/Pagination.vue';

const technologiesStore = useTechnologiesStore();
const { showToast } = useToast();
const { isLoading, withLoading } = useLoading();

const page = ref(1);
const itemsPerPage = 5;
const search = ref('');
const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const loadTechnologies = async () => {
  await withLoading(() =>
    technologiesStore.fetchTechnologies(page.value, itemsPerPage, normalizedSearch.value)
  );
};

const debouncedLoadTechnologies = debounce(loadTechnologies, '500ms');

watch(search, () => {
  page.value = 1;
  debouncedLoadTechnologies();
});

watch(page, () => {
  loadTechnologies();
});

onMounted(() => {
  loadTechnologies();
});

const technologies = computed(() => technologiesStore.technologies);
const tableHead = computed<(string | VNode)[]>(() => [
  'Imagem',
  'Tecnologia',
  'Ações',
]);

const isEditing = ref(false);
const technologyModal = ref<ModalExpose | null>(null);
const technologyBeingEdited = ref<TechnologyFormData | null>(null);

const openCreateModal = () => {
  isEditing.value = false;
  technologyBeingEdited.value = null;
  technologyModal.value?.openModal();
};

const openEditModal = (tech: TechnologyFormData) => {
  isEditing.value = true;
  technologyBeingEdited.value = { ...tech };
  technologyModal.value?.openModal();
};

const handleSubmit = async (payload: TechnologyFormData) => {
  try {
    await technologiesStore.saveTechnology({
      id: isEditing.value ? technologyBeingEdited.value?.id : undefined,
      name: payload.name,
      image: payload.image ?? undefined,
    });

    showToast(
      'success',
      isEditing.value
        ? 'Tecnologia atualizada com sucesso!'
        : 'Tecnologia adicionada com sucesso!'
    );

    technologyModal.value?.closeModal();
    await loadTechnologies();
  } catch (err) {
    console.error(err);
  }
};

const handleModalClose = () => {
  isEditing.value = false;
  technologyBeingEdited.value = null;
};

const dialogRef = ref<InstanceType<typeof Dialog> | null>(null);

const technologyToDelete = ref<number | null>(null);

const handleDeleteTechnology = (id: number) => {
  technologyToDelete.value = id;
  dialogRef.value?.openModal();
};

const deleteTechnology = async () => {
  if (!technologyToDelete.value) return;
  await technologiesStore.deleteTechnology(technologyToDelete.value);
  showToast('success', 'Tecnologia deletada com sucesso!');
  await loadTechnologies();
};

const apiUrl = import.meta.env.VITE_API_URL;

const getTechImage = (image: string) => `${apiUrl}/images/technologies/${image}`;
</script>

<template>
  <Container>
    <div class="header flex justify-between items-center flex-wrap gap-4">
      <Breadcrumb title="Tecnologias" description="Gerencie suas tecnologias aqui." />
      <div class="flex gap-2 ml-auto">
        <Button class="h-fit" @click="openCreateModal">
          <Icon name="add" class="md:mr-2" />
          <span class="hidden md:block">Nova Tecnologia</span>
        </Button>
      </div>
    </div>

    <div class="relative rounded-3xl border border-neutral dark:border-neutral-dark my-8">
      <div class="filters grid grid-cols-1 md:grid-cols-2 gap-4 w-full border-b border-neutral dark:border-neutral-dark p-5">
        <InputSearch
          v-model="search"
          label="Buscar por nome"
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
          v-if="!isLoading && technologies.length"
          :headers="tableHead"
          :items="technologies"
        >
          <template #row="{ item: tech }">
            <td class="px-6 py-4 w-[15%] max-w-[150px]">
              <img
                v-if="tech.image && typeof tech.image === 'string'"
                :src="getTechImage(tech.image)"
                alt="Technology logo"
                class="w-10 h-10 object-contain rounded"
              >
            </td>
            <td class="px-6 py-4 truncate text-font dark:text-white">
              {{ tech.name }}
            </td>
            <td class="px-6 py-4 w-[5%] min-w-[50px]">
              <div class="flex item-center gap-3">
                <button
                  class="p-2 h-10 w-10 bg-primary-accent dark:bg-primary-accent-dark text-primary dark:text-primary-dark rounded-full cursor-pointer"
                  @click="openEditModal(tech)"
                >
                  <Icon name="edit" />
                </button>
                <button
                  class="p-2 h-10 w-10 bg-danger-accent dark:bg-danger-accent-dark text-danger dark:text-danger-dark rounded-full cursor-pointer"
                  @click="handleDeleteTechnology(tech.id!)"
                >
                  <Icon name="delete" />
                </button>
              </div>
            </td>
          </template>
        </Table>
      </div>

      <div
        v-if="!isLoading && !technologies.length"
        class="text-secondary dark:text-gray-400 text-center my-10"
      >
        Nenhuma tecnologia encontrada.
      </div>
    </div>

    <Pagination
      v-if="!isLoading && technologies.length"
      v-model="page"
      :total-items="technologiesStore.totalItems"
      :items-per-page="itemsPerPage"
    />

    <Modal
      ref="technologyModal"
      :title="isEditing ? 'Editar Tecnologia' : 'Nova Tecnologia'"
      @on-modal-close="handleModalClose"
    >
      <TechnologyForm
        :mode="isEditing ? 'edit' : 'create'"
        :initial-data="technologyBeingEdited"
        @submit="handleSubmit"
        @cancel="technologyModal?.closeModal()"
      />
    </Modal>

    <Dialog
      ref="dialogRef"
      header="Tem certeza que deseja deletar esta tecnologia?"
      message="Se confirmada essa ação não poderá ser desfeita."
      @confirm-action="deleteTechnology"
    />
  </Container>
</template>
