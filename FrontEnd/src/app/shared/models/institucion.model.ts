export interface Institucion {
  id: number;
  nombre: string;
  direccion: string | null;
  telefono: string | null;
  email: string | null;
  sitio_web: string | null;
  observaciones: string | null;
  latitud: number;
  longitud: number;
  logo: string | null;
  likes: number;
  carreras?: Carrera[];
}

export interface Carrera {
  id: number;
  nombre: string;
  duracion?: string;
  descripcion?: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
}
