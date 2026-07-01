export interface ForoCategoria {
  id_categoria: number;
  nombre: string;
  descripcion: string | null;
  icono: string | null;
  color: string;
  orden: number;
  habilitado: number;
  cancelado: number;
  cantidad_temas?: number;
  idCreate?: string;
  idUpdate?: string;
}

export interface ForoTema {
  id_tema: number;
  id_categoria: number;
  id_usuario: number;
  titulo: string;
  contenido: string;
  vistas: number;
  cantidad_respuestas: number;
  cerrado: number;
  motivo_cierre: string | null;
  fijo: number;
  habilitado: number;
  cancelado: number;
  idCreate: string;
  idUpdate?: string;
  // Joins
  nombre_categoria?: string;
  icono_categoria?: string;
  color_categoria?: string;
  autor_nombre?: string;
  autor_apellido?: string;
  autor_foto?: string | null;
  autor_rol?: number;
}

export interface ForoRespuesta {
  id_respuesta: number;
  id_tema: number;
  id_usuario: number;
  contenido: string;
  citando_id: number | null;
  habilitado: number;
  cancelado: number;
  idCreate: string;
  idUpdate?: string;
  // Joins
  autor_nombre?: string;
  autor_apellido?: string;
  autor_foto?: string | null;
  autor_rol?: number;
  citando_contenido?: string;
  citando_autor_nombre?: string;
  citando_autor_apellido?: string;
}

export interface ForoAdjunto {
  id_adjunto: number;
  id_tema: number | null;
  id_respuesta: number | null;
  tipo: 'imagen' | 'pdf' | 'video';
  archivo_url: string;
  archivo_public_id: string | null;
  archivo_nombre_original: string;
  archivo_tamano_bytes: number;
  habilitado: number;
  idCreate: string;
}

export interface ForoListadoResponse {
  success: boolean;
  temas: ForoTema[];
  total: number;
  page: number;
  limit: number;
  pages: number;
  message?: string;
}

export interface ForoRespuestasResponse {
  success: boolean;
  respuestas: ForoRespuesta[];
  total: number;
  page: number;
  limit: number;
  pages: number;
}

export interface ForoTemaResponse {
  success: boolean;
  tema: ForoTema;
  adjuntos: ForoAdjunto[];
}

export interface ForoCategoriaResponse {
  success: boolean;
  categorias: ForoCategoria[];
}

export interface ForoBusquedaResponse {
  success: boolean;
  temas?: ForoTema[];
  respuestas?: ForoRespuesta[];
  total: number;
  page: number;
  limit: number;
  pages: number;
  termino: string;
}
