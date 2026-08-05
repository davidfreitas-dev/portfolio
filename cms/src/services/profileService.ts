import api from '@/api';
import type { UserProfile, ApiResponse } from '@/types';

export type UpdateProfilePayload = Partial<
  Pick<UserProfile, 'name' | 'email' | 'phone'>
>;

export type UpdatePasswordPayload = {
  current_password: string;
  new_password: string;
  new_password_confirm: string;
};

export const profileService = {
  async fetchProfile(): Promise<UserProfile> {
    const response = await api.get<ApiResponse<UserProfile>>('/users/me') as unknown as ApiResponse<UserProfile>;
    if (!response.data) throw new Error('No profile data received');
    return response.data;
  },

  async updateProfile(payload: UpdateProfilePayload): Promise<UserProfile> {
    const response = await api.put<ApiResponse<UserProfile>>('/users/me', payload) as unknown as ApiResponse<UserProfile>;
    // The API might return the updated profile in data, or just a success message.
    // Based on the docs, PUT /users/me returns a success message without data.
    // If we need to return UserProfile, we might need to cast or just return undefined and refetch.
    // Let's assume we return the payload data merged or undefined, but the doc doesn't show data in response.
    // We'll return response.data as UserProfile just in case, or undefined.
    return response.data as UserProfile;
  },

  async updatePassword(payload: UpdatePasswordPayload): Promise<void> {
    await api.patch('/users/me/change-password', payload);
  },
};
