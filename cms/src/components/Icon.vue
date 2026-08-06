<script setup lang="ts">
import { computed } from 'vue';
import * as icons from 'lucide-vue-next';

defineOptions({ 
  inheritAttrs: false 
});

const props = defineProps<{
 name: string;
}>();

const iconMap: Record<string, keyof typeof icons> = {
  'visibility_off': 'EyeOff',
  'visibility': 'Eye',
  'check': 'Check',
  'add': 'Plus',
  'edit': 'Pencil',
  'delete': 'Trash',
  'logout': 'LogOut',
  'close': 'X',
  'warning': 'TriangleAlert',
  'info': 'Info',
  'chevron_left': 'ChevronLeft',
  'home': 'Home',
  'hub': 'Waypoints',
  'code': 'Code',
  'handyman': 'Wrench',
  'settings': 'Settings',
  'keyboard_arrow_up': 'ChevronUp',
  'keyboard_arrow_down': 'ChevronDown',
  'check_box': 'CheckSquare',
  'check_box_outline_blank': 'Square',
  'search': 'Search',
  'light_mode': 'Sun',
  'dark_mode': 'Moon',
  'person': 'User',
  'dashboard': 'LayoutDashboard',
  'menu': 'Menu',
  'expand_more': 'ChevronDown',
  'cloud_done': 'CloudRain'
};

const iconComponent = computed<any>(() => {
  const mappedName = iconMap[props.name];
  if (mappedName && icons[mappedName]) {
    return icons[mappedName];
  }
 
  // Fallback to trying to format the name
  const formattedName = props.name.replace(/(^\w|-\w)/g, (c) => c.replace('-', '').toUpperCase()) + 'Icon';
  return icons[formattedName as keyof typeof icons] || icons[props.name as keyof typeof icons] || icons.HelpCircle;
});
</script>

<template>
  <component :is="iconComponent" v-bind="$attrs" />
</template>
