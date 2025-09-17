import { type App } from 'vue';
import filters from '@/filters';

export default {
  install(app: App) {
    app.config.globalProperties.$filters = filters;
  }
};
