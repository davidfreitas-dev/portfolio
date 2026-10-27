<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useLoading } from '@/composables/useLoading';
import { useToast } from '@/composables/useToast';
import { useProjectsStore } from '@/stores/projectsStore';
import Container from '@/components/Container.vue';
import Button from '@/components/Button.vue';
import Icon from '@/components/Icon.vue';
import Table from '@/components/Table.vue';
import Pagination from '@/components/Pagination.vue';
import Modal, { type ModalExpose } from '@/components/Modal.vue';
import Dialog from '@/components/Dialog.vue';
import ProjectForm, { type ProjectFormData } from '@/forms/ProjectForm.vue';
import ProjectFilters from './ProjectFilters.vue';
import { useProjects } from './useProjects';
import type { Project } from '@/types';

const projectsStore = useProjectsStore();
const { isLoading, withLoading } = useLoading();
const { showToast } = useToast();

const modalRef = ref<ModalExpose | null>(null);
const dialogRef = ref<{ openModal: () => void } | null>(null);
const targetId = ref<number | null>(null);

const { page, itemsPerPage, search, selectedStatus, loadProjects } = useProjects();

const handleOpenCreate = (): void => {
  modalRef.value?.openModal();
};

const handleSubmit = async (payload: ProjectFormData): Promise<void> => {
  await withLoading(async () => {
    await projectsStore.saveProject(payload);
    modalRef.value?.closeModal();
    showToast('success', 'Projeto salvo com sucesso!');
    await loadProjects();
  });
};

const handleAskDelete = (project: Project): void => {
  targetId.value = project.id;
  dialogRef.value?.openModal();
};

const handleConfirmDelete = async (): Promise<void> => {
  if (!targetId.value) return;

  await withLoading(async () => {
    await projectsStore.deleteProject(targetId.value!);
    showToast('success', 'Projeto removido com sucesso!');
    await loadProjects();
  });
};

onMounted(async () => {
  await loadProjects();
});
</script>

<template>
  <Container class="pb-20">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Projetos</h1>
      <Button variant="fill" color="primary" @click="handleOpenCreate">
        <template #left-icon>
          <Icon name="add" class="w-5 h-5" />
        </template>
        Novo Projeto
      </Button>
    </div>

    <!-- Filtros extraídos -->
    <ProjectFilters
      v-model:search="search"
      v-model:status="selectedStatus"
    />

    <!-- Tabela do Design System -->
    <Table
      :items="projectsStore.projects"
      :loading="isLoading"
      @delete="handleAskDelete"
    />

    <div class="mt-6">
      <Pagination
        v-model="page"
        :total-items="projectsStore.totalItems"
        :items-per-page="itemsPerPage"
      />
    </div>

    <!-- Modal com formulário dedicado -->
    <Modal ref="modalRef" title="Novo Projeto" align="center">
      <ProjectForm
        :loading="isLoading"
        @submit="handleSubmit"
        @cancel="modalRef?.closeModal()"
      />
    </Modal>

    <!-- Diálogo de confirmação -->
    <Dialog
      ref="dialogRef"
      header="Excluir Projeto"
      message="Tem certeza que deseja remover este projeto? Esta ação não pode ser desfeita."
      confirm-text="Excluir"
      @confirm-action="handleConfirmDelete"
    />
  </Container>
</template>
