/** Envelope padrão de toda resposta da API */
export interface ApiResponse<T = unknown> {
  code: number;
  status: 'success' | 'error' | 'fail';
  message: string;
  data?: T;
}

/** Interface base para dados paginados da API */
export interface PaginatedData {
  total: number;
  page: number;
  limit: number;
  pages: number;
}

export interface Option {
  label: string;
  value: string | number;
}

export type ToastType = 'success' | 'error' | 'info' | 'warning';

export interface ToastData {
  type: ToastType;
  message: string;
}
