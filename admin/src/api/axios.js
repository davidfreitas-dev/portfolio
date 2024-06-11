import axios from 'axios';

const BASE_URL = '';

const instance = axios.create({
  baseURL: BASE_URL,
  timeout: 10000,
});

instance.interceptors.request.use(
  (request) => {
    request.headers['X-Token'] = '';
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
    return Promise.reject(error);
  }
);

export default instance;
