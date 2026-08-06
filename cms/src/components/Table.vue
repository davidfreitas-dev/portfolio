<script setup lang="ts" generic="T">
import { type VNode } from 'vue';
defineProps<{
 headers: (string | VNode)[];
 items: T[];
}>();
</script>

<template>
  <div class="overflow-x-auto scrollbar">
    <table class="min-w-full text-left">
      <thead class="border-b border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-200">
        <tr>
          <th
            v-for="(header, index) in headers"
            :key="index"
            :class="[
              'px-6 py-4 text-gray-700 dark:text-gray-100',
              index === 0 && 'pr-0'
            ]"
          >
            <component 
              :is="header"
              v-if="typeof header !== 'string'"
            />
            <span v-else>{{ header }}</span>
          </th>
        </tr>
      </thead>
      <tbody class="text-gray-400 dark:text-gray-300 text-base">
        <tr
          v-for="(item, index) in items"
          :key="index"
          :class="index !== items.length - 1 ? 'border-b border-gray-200 dark:border-gray-600' : ''"
        >
          <slot
            name="row"
            :item="item"
            :index="index"
          />
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style socped>
.scrollbar::-webkit-scrollbar {
 height: 8px;
 background-color: transparent;
}

.scrollbar::-webkit-scrollbar-thumb {
 background-color: #eaeaea; 
 border-radius: 4px;
}

.dark .scrollbar::-webkit-scrollbar-thumb {
 background-color: #3a3a3a; 
}
</style>
