<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { useProfileStore } from '@/stores/profileStore';
import Container from '@/components/Container.vue';
import Breadcrumb from '@/components/Breadcrumb.vue';

const profileStore = useProfileStore();

const { user } = storeToRefs(profileStore);
</script>

<template>
  <Container>
    <Breadcrumb :title="`Olá, ${user?.name || 'Convidado'}! 👋`" description="É bom te ver de novo." />
  </Container>
</template>