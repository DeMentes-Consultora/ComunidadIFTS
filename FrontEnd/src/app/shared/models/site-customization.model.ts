export interface SiteNavbarConfig {
  id_navbar: number | null;
  brand_text: string;
  logo_url: string | null;
  logo_public_id: string | null;
  habilitado: number;
}

export interface SiteCarouselItem {
  id_carrousel: number;
  titulo: string;
  descripcion: string;
  enlace: string | null;
  orden_visual: number;
  foto_perfil_url: string | null;
  foto_perfil_public_id: string | null;
  habilitado: number;
}

export interface SiteCustomizationConfig {
  navbar: SiteNavbarConfig;
  carousel: SiteCarouselItem[];
}

export interface DashboardStats {
  usuarios_registrados: number;
  alumnos: number;
  administradores: number;
  pendientes_aprobacion: number;
  instituciones: number;
  carreras: number;
}

export interface SiteCustomizationResponse<T> {
  success: boolean;
  message?: string;
  data?: T;
}

export interface SiteCustomizationSavePayload {
  navbar: {
    brand_text: string;
    remove_logo?: boolean;
  };
  carousel: Array<{
    id_carrousel?: number | null;
    client_key: string;
    titulo: string;
    descripcion: string;
    enlace: string;
    orden_visual: number;
    habilitado: boolean;
    remove_image?: boolean;
  }>;
}