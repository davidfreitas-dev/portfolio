<script setup lang="ts">
import { ref, computed, onMounted, type VNode, watch } from 'vue';
import { debounce } from 'vue-debounce';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import { useExperiencesStore } from '@/stores/experiencesStore';
import Container from '@/components/Container.vue';
import Breadcrumb from '@/components/Breadcrumb.vue';
import Icon from '@/components/Icon.vue';
import Button from '@/components/Button.vue';
import InputSearch from '@/components/InputSearch.vue';
import Table from '@/components/Table.vue';
import Loader from '@/components/Loader.vue';
import ExperienceForm, { type ExperienceFormData } from '@/forms/ExperienceForm.vue';
import Modal, { type ModalExpose } from '@/components/Modal.vue';
import Dialog from '@/components/Dialog.vue';
import Pagination from '@/components/Pagination.vue';

const experiencesStore = useExperiencesStore();
const { showToast } = useToast();
const { isLoading, withLoading } = useLoading();

const page = ref(1);
const itemsPerPage = 5;
const search = ref('');
const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const loadExperiences = async () => {
  await withLoading(() =>
    experiencesStore.fetchExperiences(page.value, itemsPerPage, normalizedSearch.value)
  );
};

const debouncedLoadExperiences = debounce(loadExperiences, '500ms');

watch(search, () => {
  page.value = 1;
  debouncedLoadExperiences();
});

watch(page, () => {
  loadExperiences();
});

onMounted(() => {
  loadExperiences();
});

const experiences = computed(() => experiencesStore.experiences);
const tableHead = computed<(string | VNode)[]>(() => [
  'Título',
  'Descrição',
  'Período',
  'Ações',
]);

const isEditing = ref(false);
const experienceModal = ref<ModalExpose | null>(null);
const experienceBeingEdited = ref<ExperienceFormData | null>(null);

const openCreateModal = () => {
  isEditing.value = false;
  experienceBeingEdited.value = null;
  experienceModal.value?.openModal();
};

const openEditModal = (exp: ExperienceFormData) => {
  isEditing.value = true;
  experienceBeingEdited.value = { ...exp };
  experienceModal.value?.openModal();
};

const handleSubmit = async (payload: ExperienceFormData) => {
  try {
    if (isEditing.value && experienceBeingEdited.value?.id) {
      await experiencesStore.updateExperience(experienceBeingEdited.value.id, payload);
      showToast('success', 'Experiência atualizada com sucesso!');
    } else {
      await experiencesStore.createExperience(payload);
      showToast('success', 'Experiência adicionada com sucesso!');
    }
    experienceModal.value?.closeModal();
    await loadExperiences();
  } catch(err) {
    console.error(err);
  }
};

const handleModalClose = () => {
  isEditing.value = false;
  experienceBeingEdited.value = null;
};

const dialogRef = ref<InstanceType<typeof Dialog> | null>(null);
const experienceToDelete = ref<number | null>(null);

const handleDeleteExperience = (id: number) => {
  experienceToDelete.value = id;
  dialogRef.value?.openModal();
};

const deleteExperience = async () => {
  if (!experienceToDelete.value) return;
  await experiencesStore.deleteExperience(experienceToDelete.value);
  showToast('success', 'Experiência deletada com sucesso!');
  await loadExperiences();
};
</script>

<template>
  <Container>
    <div class="header flex justify-between items-center flex-wrap gap-4">
      <Breadcrumb title="Experiências" description="Gerencie suas experiências aqui." />
      <div class="flex gap-2 ml-auto">
        <Button class="h-fit" @click="openCreateModal">
          <Icon name="add" class="md:mr-2" />
          <span class="hidden md:block">Nova Experiência</span>
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
          v-if="!isLoading && experiences.length"
          :headers="tableHead"
          :items="experiences"
        >
          <template #row="{ item: exp }">
            <td class="px-6 py-4 max-w-[200px] truncate text-font dark:text-white">
              {{ exp.title }}
            </td>
            <td class="px-6 py-4 max-w-[300px] truncate text-font dark:text-white">
              {{ exp.description }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-font dark:text-white">
              {{ $filters.formatPeriod([exp.start_date, exp.end_date]) }}
            </td>
            <td class="px-6 py-4">
              <div class="flex item-center gap-3">
                <button
                  class="p-2 h-10 w-10 bg-primary-accent dark:bg-primary-accent-dark text-primary dark:text-primary-dark rounded-full cursor-pointer"
                  @click="openEditModal(exp)"
                >
                  <Icon name="edit" />
                </button>
                <button
                  class="p-2 h-10 w-10 bg-danger-accent dark:bg-danger-accent-dark text-danger dark:text-danger-dark rounded-full cursor-pointer"
                  @click="handleDeleteExperience(exp.id!)"
                >
                  <Icon name="delete" />
                </button>
              </div>
            </td>
          </template>
        </Table>
      </div>

      <div
        v-if="!isLoading && !experiences.length"
        class="text-secondary dark:text-gray-400 text-center my-10"
      >
        Nenhuma experiência encontrada.
      </div>
    </div>

    <Pagination
      v-if="!isLoading && experiences.length"
      v-model="page"
      :total-items="experiencesStore.totalItems"
      :items-per-page="itemsPerPage"
    />
    
    <Modal
      ref="experienceModal"
      :title="isEditing ? 'Editar Experiência' : 'Nova Experiência'"
      @on-modal-close="handleModalClose"
    >
      <ExperienceForm
        :mode="isEditing ? 'edit' : 'create'"
        :initial-data="experienceBeingEdited"
        @submit="handleSubmit"
        @cancel="experienceModal?.closeModal()"
      />
    </Modal>
    
    <Dialog
      ref="dialogRef"
      header="Tem certeza que deseja deletar esta experiência?"
      message="Se confirmada essa ação não poderá ser desfeita."
      @confirm-action="deleteExperience"
    />
  </Container>
</template>
