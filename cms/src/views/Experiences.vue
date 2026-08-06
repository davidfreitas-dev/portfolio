<script setup lang="ts">
import { ref, computed, onMounted, type VNode, watch } from 'vue';
import { debounce } from 'vue-debounce';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import { useExperiencesStore } from '@/stores/experiencesStore';
import type { CreateExperiencePayload } from '@/services/experienceService';
import dayjs from 'dayjs';
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

import type { Experience } from '@/types';

const openEditModal = (exp: Experience) => {
  isEditing.value = true;
  experienceBeingEdited.value = { ...exp, end_date: exp.end_date ?? null } as unknown as ExperienceFormData;
  experienceModal.value?.openModal();
};

const handleSubmit = async (payload: ExperienceFormData) => {
  try {
    await withLoading(async () => {
      const apiPayload: CreateExperiencePayload = {
        title: payload.title,
        description: payload.description,
        start_date: dayjs(payload.start_date).format('YYYY-MM-DD'),
        end_date: payload.end_date ? dayjs(payload.end_date).format('YYYY-MM-DD') : null,
        sort_order: 0
      };

      if (isEditing.value && experienceBeingEdited.value?.id) {
        await experiencesStore.updateExperience(experienceBeingEdited.value.id, apiPayload);
        showToast('success', 'Experiência atualizada com sucesso!');
      } else {
        await experiencesStore.createExperience(apiPayload);
        showToast('success', 'Experiência adicionada com sucesso!');
      }
    });
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
  await withLoading(async () => {
    await experiencesStore.deleteExperience(experienceToDelete.value!);
  });
  showToast('success', 'Experiência deletada com sucesso!');
  await loadExperiences();
};
</script>

<template>
  <Container>
    <div class="header flex justify-between items-center flex-wrap gap-4">
      <Breadcrumb title="Experiências" description="Gerencie suas experiências aqui." />
      <div class="flex gap-2 ml-auto">
        <Button @click="openCreateModal">
          <Icon name="add" class="md:mr-2" />
          <span class="hidden md:block">Nova Experiência</span>
        </Button>
      </div>
    </div>
 
    <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-md my-8">
      <div class="filters grid grid-cols-1 md:grid-cols-2 gap-4 w-full border-b border-gray-200 dark:border-gray-600 p-5">
        <InputSearch
          v-model="search"
          placeholder="Buscar por título"
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
            <td class="px-6 py-4 max-w-[200px] truncate text-gray-700 dark:text-gray-100">
              {{ exp.title }}
            </td>
            <td class="px-6 py-4 max-w-[300px] truncate text-gray-500 dark:text-gray-300">
              {{ exp.description }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-300">
              {{ $filters.formatPeriod([exp.start_date, exp.end_date]) }}
            </td>
            <td class="px-6 py-4 w-[5%] min-w-[50px]">
              <div class="flex items-center gap-2">
                <button
                  class="flex items-center justify-center h-9 w-9 text-gray-400 dark:text-gray-500 hover:text-primary-default dark:hover:text-primary-default hover:bg-primary-50 dark:hover:bg-gray-700/50 rounded-lg transition-all duration-200 hover:scale-105"
                  title="Editar"
                  @click="openEditModal(exp)"
                >
                  <Icon name="edit" class="w-4 h-4" />
                </button>
                <button
                  class="flex items-center justify-center h-9 w-9 text-gray-400 dark:text-gray-500 hover:text-danger dark:hover:text-danger hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200 hover:scale-105"
                  title="Deletar"
                  @click="handleDeleteExperience(exp.id!)"
                >
                  <Icon name="delete" class="w-4 h-4" />
                </button>
              </div>
            </td>
          </template>
        </Table>
      </div>

      <div
        v-if="!isLoading && !experiences.length"
        class="text-gray-500 dark:text-gray-300 text-center py-10"
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
