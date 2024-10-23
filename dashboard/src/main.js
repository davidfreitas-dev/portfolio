import { createApp } from 'vue';
import { createPinia } from 'pinia';

import dayjs from 'dayjs';
import 'dayjs/locale/pt-br';
import './style.css';

import App from './App.vue';
import router from './router';

const pinia = createPinia();
const app = createApp(App);

app.config.globalProperties.$filters = {
  formatDate(date) { 
    return dayjs(date, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YYYY HH:mm:ss');   
  },
  formatDateMonthYear(date) { 
    const [month, year] = date.split('/');
    return dayjs(`${year}-${month}-01`).locale('pt-br').format('MMM YYYY').toUpperCase();   
  }
};

app.use(pinia);
app.use(router);
app.mount('#app');
