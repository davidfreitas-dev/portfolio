import filters from '@/utils/filters';

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $filters: typeof filters;
  }
}