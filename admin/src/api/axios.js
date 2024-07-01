import axios from 'axios';
import { useSessionStore } from '../stores/session';

const storeSession = useSessionStore();

const BASE_URL = import.meta.env.VITE_FIREBASE_BASE_URL;

const instance = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
});

instance.interceptors.request.use(
  (request) => {
    request.headers['X-Token'] = storeSession.session && storeSession.session.token ? storeSession.session.token : '';
    return request;
  },
  (error) => {
    console.error('Erro na requisicao da API:', error);
    return Promise.reject(error);
  }
);

instance.interceptors.response.use(
  (response) => {
    return response.data;
  },
  (error) => {
    console.error('Erro na resposta da API:', error);
    return Promise.reject(error.response);
  }
);

export default instance;
