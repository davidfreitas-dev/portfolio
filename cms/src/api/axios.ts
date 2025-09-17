import axios, { type InternalAxiosRequestConfig, type AxiosResponse } from 'axios';
import { useAuthStore } from '@/stores/authStore';

const BASE_URL = import.meta.env.VITE_API_URL as string;

const instance = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
});

instance.interceptors.request.use(
  (request: InternalAxiosRequestConfig) => {
    const authStore = useAuthStore();
    const token = authStore.token;

    if (!request.headers) request.headers = new axios.AxiosHeaders();
    if (token) request.headers.set('Authorization', `Bearer ${token}`);
  
    return request;
  },
  (error) => {
    console.error('Erro na requisição da API:', error);
    return Promise.reject(error);
  }
);

instance.interceptors.response.use(
  (response: AxiosResponse) => {
    return response.data;
  },
  (error) => {
    console.error('Erro na resposta da API:', error);
    return Promise.reject(error.response);
  }
);

export default instance;
