export interface UserProfile {
  id: number;
  name: string;
  email: string;
  phone?: string;
  role: string;
}

export interface AuthData {
  token: string;
  user: UserProfile;
}

export interface AuthTokensData {
  access_token: string;
}

export interface LoginCredentials {
  email: string;
  password?: string;
  otp?: string;
}

export interface RequestLoginPayload {
  email: string;
}

export interface ForgotPasswordPayload {
  email: string;
}

export interface ValidateResetCodePayload {
  email: string;
  code: string;
}

export interface ResetPasswordPayload {
  email: string;
  code: string;
  password: string;
  password_confirm: string;
}
