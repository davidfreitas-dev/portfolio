import axios, {
  type AxiosInstance,
  type AxiosResponse,
  type InternalAxiosRequestConfig,
  type AxiosRequestConfig,
  type AxiosError,
} from 'axios';
import { useAuthStore } from '@/stores/authStore';
import type { ApiResponse, AuthTokensData } from '@/types';

// Typed API instance
export interface ApiInstance extends AxiosInstance {
  get<T = unknown, R = ApiResponse<T>, D = unknown>(url: string, config?: AxiosRequestConfig<D>): Promise<R>;
  post<T = unknown, R = ApiResponse<T>, D = unknown>(url: string, data?: D, config?: AxiosRequestConfig<D>): Promise<R>;
  put<T = unknown, R = ApiResponse<T>, D = unknown>(url: string, data?: D, config?: AxiosRequestConfig<D>): Promise<R>;
  delete<T = unknown, R = ApiResponse<T>, D = unknown>(url: string, config?: AxiosRequestConfig<D>): Promise<R>;
  patch<T = unknown, R = ApiResponse<T>, D = unknown>(url: string, data?: D, config?: AxiosRequestConfig<D>): Promise<R>;
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 10_000,
  withCredentials: true,
}) as unknown as ApiInstance;

// Token refresh queue
type QueueEntry = { resolve: (token: string) => void; reject: (error: unknown) => void };

let isRefreshing = false;
let failedQueue: QueueEntry[] = [];

const processQueue = (error: unknown, token?: string): void => {
  failedQueue.forEach(({ resolve, reject }) => (error ? reject(error) : resolve(token!)));
  failedQueue = [];
};

// Token refresh logic
async function refreshAccessToken(): Promise<string> {
  const authStore = useAuthStore();

  const { data } = await axios.post<ApiResponse<AuthTokensData>>(
    `${(api as AxiosInstance).defaults.baseURL}/auth/refresh`,
    undefined,
    { withCredentials: true }
  );

  const { access_token } = data.data;
  authStore.setToken(access_token);

  return access_token;
}

// Interceptors
(api as AxiosInstance).interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const { accessToken } = useAuthStore();

  if (accessToken && config.headers) {
    config.headers.Authorization = `Bearer ${accessToken}`;
  }

  return config;
});

(api as AxiosInstance).interceptors.response.use(
  (response: AxiosResponse<ApiResponse>) => response.data as unknown as AxiosResponse,
  async (error: AxiosError<ApiResponse>) => {
    if (!error.response || !error.config) return Promise.reject(error);

    if (error.response.status !== 401 || error.config.url?.includes('/auth/')) {
      return Promise.reject(error);
    }

    if (isRefreshing) {
      const token = await new Promise<string>((resolve, reject) =>
        failedQueue.push({ resolve, reject })
      );
      error.config.headers.Authorization = `Bearer ${token}`;
      return api(error.config as AxiosRequestConfig);
    }

    isRefreshing = true;

    try {
      const newToken = await refreshAccessToken();
      processQueue(null, newToken);
      error.config.headers.Authorization = `Bearer ${newToken}`;
      return api(error.config as AxiosRequestConfig);
    } catch (refreshError) {
      processQueue(refreshError);
      useAuthStore().clearSession();
      window.location.href = '/login';
      return Promise.reject(refreshError);
    } finally {
      isRefreshing = false;
    }
  }
);

export default api;