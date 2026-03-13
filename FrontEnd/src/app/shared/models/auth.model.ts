export interface LoginRequest {
  email: string;
  clave: string;
}

export interface RegisterRequest {
  nombre: string;
  apellido: string;
  dni: string;
  fecha_nacimiento: string;
  telefono: string;
  id_institucion: number;
  email: string;
  clave: string;
  confirmar_clave: string;
}

export interface AuthUser {
  id_usuario: number;
  email: string;
  id_rol: number;
  nombre_rol: string;
  id_persona: number;
  id_institucion: number;
  nombre_institucion: string;
  nombre: string;
  apellido: string;
  dni: string;
  telefono: string;
  edad: number;
  fecha_nacimiento: string;
  foto_perfil_url?: string | null;
  foto_perfil_public_id?: string | null;
  habilitado: number;
  cancelado: number;
}

export interface AuthResponse {
  success: boolean;
  message?: string;
  data?: AuthUser;
}

export interface RegisterResponse {
  success: boolean;
  message?: string;
  data?: AuthUser;
  pendiente_aprobacion?: boolean;
  email_admin_notificado?: boolean;
  warning?: string | null;
}

export interface BasicAuthResponse {
  success: boolean;
  message?: string;
}

export interface GoogleIdentity {
  idToken: string;
  email: string;
  nombre?: string;
  apellido?: string;
  fotoPerfilUrl?: string;
}

export interface GoogleLoginRequest {
  mode: 'login';
  id_token: string;
}

export interface GoogleRegisterRequest {
  mode: 'register';
  id_token: string;
  nombre: string;
  apellido: string;
  dni: string;
  fecha_nacimiento: string;
  telefono: string;
  id_institucion: number;
  foto_perfil_url?: string;
}
