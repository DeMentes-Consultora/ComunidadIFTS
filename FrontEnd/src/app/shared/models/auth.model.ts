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
  habilitado: number;
  cancelado: number;
}

export interface AuthResponse {
  success: boolean;
  message?: string;
  data?: AuthUser;
}

export interface BasicAuthResponse {
  success: boolean;
  message?: string;
}
