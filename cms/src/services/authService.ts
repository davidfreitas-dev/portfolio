import api from '@/api';
import type {
  ApiResponse,
  AuthData,
  LoginCredentials,
  RequestLoginPayload,
  ForgotPasswordPayload,
  ResetPasswordPayload,
  ValidateResetCodePayload,
} from '@/types';

export const authService = {
  async requestLogin(payload: RequestLoginPayload): Promise<void> {
    await api.post<ApiResponse>('/auth/request-login', payload);
  },

  async login(credentials: LoginCredentials): Promise<AuthData> {
    // api returns ApiResponse directly due to interceptor unwrapping response.data
    const response = await api.post<ApiResponse<AuthData>>('/auth/login', credentials) as unknown as ApiResponse<AuthData>;
    if (!response.data) throw new Error('No data received');
    return response.data;
  },

  async logout(): Promise<void> {
    await api.post('/auth/logout');
  },

  async forgotPassword(payload: ForgotPasswordPayload): Promise<void> {
    await api.post('/auth/forgot', payload);
  },

  async validateResetCode(payload: ValidateResetCodePayload): Promise<void> {
    await api.post('/auth/validate-reset-code', payload);
  },

  async resetPassword(payload: ResetPasswordPayload): Promise<void> {
    await api.post('/auth/reset', payload);
  },
};
