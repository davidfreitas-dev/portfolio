export interface Role {
  id: number;
  name: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string | null;
  cpfcnpj: string;
  is_active: number;
  created_at: string;
  updated_at: string;
  roles: Role[];
}