export interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  avatar: string | null;
  is_active: boolean;
  tenant_id: number;
  roles: string[];
  permissions: string[];
  created_at: string;
  updated_at: string;
}

export interface LoginPayload {
  email: string;
  password: string;
}

export interface RegisterPayload {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  company_name: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}
