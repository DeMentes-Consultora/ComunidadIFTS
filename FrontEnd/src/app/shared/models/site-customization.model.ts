export interface SiteNavbarConfig {
  id_navbar: number | null;
  brand_text: string;
  logo_url: string | null;
  logo_public_id: string | null;
  habilitado: number;
}

export interface SiteSidebarConfig {
  id_sidebar: number | null;
  brand_text: string;
  logo_url: string | null;
  logo_public_id: string | null;
  habilitado: number;
}

export interface SiteFooterBrandingConfig {
  id_footer_branding: number | null;
  developer_text: string;
  link_url: string | null;
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
  sidebar: SiteSidebarConfig;
  footer_branding: SiteFooterBrandingConfig;
  carousel: SiteCarouselItem[];
  shop_carousel: SiteCarouselItem[];
  shop_gallery: SiteCarouselItem[];
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
    logo_selected?: boolean;
  };
  sidebar: {
    brand_text: string;
    remove_logo?: boolean;
    logo_selected?: boolean;
  };
  footer_branding: {
    developer_text: string;
    link_url: string;
    remove_logo?: boolean;
    logo_selected?: boolean;
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
  shop_carousel: Array<{
    id_carrousel?: number | null;
    client_key: string;
    titulo: string;
    descripcion: string;
    enlace: string;
    orden_visual: number;
    habilitado: boolean;
    remove_image?: boolean;
  }>;
  shop_gallery: Array<{
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