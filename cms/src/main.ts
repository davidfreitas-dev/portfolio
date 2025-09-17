import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

import App from './App.vue';
import router from '@/router';
import { useAuthStore } from '@/stores/authStore';

import './style.css';
import './tailwind.css';
import filters from '@/plugins/filters';

const initApp = async () => {
  const app = createApp(App);

  const pinia = createPinia();
  pinia.use(piniaPluginPersistedstate);

  app.use(pinia);
  app.use(filters);

  const authStore = useAuthStore();
  await authStore.initAuth();

  app.use(router);
  app.mount('#app');
};

initApp();
