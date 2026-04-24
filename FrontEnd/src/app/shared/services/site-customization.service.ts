import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable, of } from 'rxjs';
import { map, tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import {
  DashboardStats,
  SiteCustomizationConfig,
  SiteCustomizationResponse,
  SiteCustomizationSavePayload,
} from '../models/site-customization.model';

@Injectable({
  providedIn: 'root'
})
export class SiteCustomizationService {
  private readonly apiUrl = `${environment.apiUrl}/site-customization.php`;
  private readonly statsUrl = `${environment.apiUrl}/dashboard-stats.php`;
  private readonly defaultConfig: SiteCustomizationConfig = {
    navbar: {
      id_navbar: null,
      brand_text: '',
      logo_url: null,
      logo_public_id: null,
      habilitado: 1,
    },
    sidebar: {
      id_sidebar: null,
      brand_text: '',
      logo_url: null,
      logo_public_id: null,
      habilitado: 1,
    },
    footer_branding: {
      id_footer_branding: null,
      developer_text: 'Desarrollado por DeMentesConsultora',
      link_url: null,
      logo_url: null,
      logo_public_id: null,
      habilitado: 1,
    },
    carousel: [],
    shop_carousel: [],
    shop_gallery: [],
  };
  private readonly siteConfigSubject = new BehaviorSubject<SiteCustomizationConfig>(this.defaultConfig);
  private loaded = false;

  readonly siteConfig$ = this.siteConfigSubject.asObservable();

  constructor(private http: HttpClient) {}

  private normalizeConfig(config: Partial<SiteCustomizationConfig> | null | undefined): SiteCustomizationConfig {
    const navbar = config?.navbar ?? {};
    const sidebar = config?.sidebar ?? {};
    const footerBranding = config?.footer_branding ?? {};
    const carousel = Array.isArray(config?.carousel) ? config!.carousel : [];
    const shopCarousel = Array.isArray(config?.shop_carousel) ? config!.shop_carousel : [];
    const shopGallery = Array.isArray(config?.shop_gallery) ? config!.shop_gallery : [];

    return {
      navbar: {
        ...this.defaultConfig.navbar,
        ...navbar,
      },
      sidebar: {
        ...this.defaultConfig.sidebar,
        ...sidebar,
      },
      footer_branding: {
        ...this.defaultConfig.footer_branding,
        ...footerBranding,
      },
      carousel,
      shop_carousel: shopCarousel,
      shop_gallery: shopGallery,
    };
  }

  loadPublicConfig(force = false): Observable<SiteCustomizationConfig> {
    if (this.loaded && !force) {
      return of(this.siteConfigSubject.value);
    }

    return this.http.get(this.apiUrl, { responseType: 'text' }).pipe(
      map((rawResponse) => {
        const response = this.parseApiResponse<SiteCustomizationConfig>(rawResponse);
        if (!response.success) {
          throw new Error(response.message || 'No fue posible obtener la configuracion publica');
        }
        return this.normalizeConfig(response.data);
      }),
      tap((config) => {
        this.loaded = true;
        this.siteConfigSubject.next(config);
      })
    );
  }

  getAdminConfig(): Observable<SiteCustomizationConfig> {
    return this.http.get<SiteCustomizationResponse<SiteCustomizationConfig>>(`${this.apiUrl}?scope=admin`, { withCredentials: true }).pipe(
      map((response) => {
        if (!response.success) {
          throw new Error(response.message || 'No fue posible obtener la configuracion de administracion');
        }
        return this.normalizeConfig(response.data);
      })
    );
  }

  saveSiteConfig(
    payload: SiteCustomizationSavePayload,
    files: {
      navbarLogo?: File | null;
      sidebarLogo?: File | null;
      footerBrandingLogo?: File | null;
      carouselFiles?: Record<string, File | null>;
      shopCarouselFiles?: Record<string, File | null>;
      shopGalleryFiles?: Record<string, File | null>;
    }
  ): Observable<SiteCustomizationConfig> {
    const formData = new FormData();
    formData.append('payload', JSON.stringify(payload));

    if (files.navbarLogo) {
      formData.append('navbar_logo', files.navbarLogo);
    }

    if (files.sidebarLogo) {
      formData.append('sidebar_logo', files.sidebarLogo);
    }

    if (files.footerBrandingLogo) {
      formData.append('footer_branding_logo', files.footerBrandingLogo);
    }

    Object.entries(files.carouselFiles ?? {}).forEach(([clientKey, file]) => {
      if (!file) {
        return;
      }

      formData.append(`carousel_image_${clientKey}`, file);
    });

    Object.entries(files.shopCarouselFiles ?? {}).forEach(([clientKey, file]) => {
      if (!file) {
        return;
      }

      formData.append(`shop_carousel_image_${clientKey}`, file);
    });

    Object.entries(files.shopGalleryFiles ?? {}).forEach(([clientKey, file]) => {
      if (!file) {
        return;
      }

      formData.append(`shop_gallery_image_${clientKey}`, file);
    });

    return this.http.post<SiteCustomizationResponse<SiteCustomizationConfig>>(this.apiUrl, formData, { withCredentials: true }).pipe(
      map((response) => {
        if (!response.success) {
          throw new Error(response.message || 'No fue posible guardar la configuracion del sitio');
        }
        return this.normalizeConfig(response.data);
      }),
      tap((adminConfig) => {
        this.loaded = true;
        this.siteConfigSubject.next({
          navbar: adminConfig.navbar,
          sidebar: adminConfig.sidebar,
          footer_branding: adminConfig.footer_branding,
          carousel: adminConfig.carousel.filter((slide) => slide.habilitado === 1),
          shop_carousel: adminConfig.shop_carousel.filter((slide) => slide.habilitado === 1),
          shop_gallery: adminConfig.shop_gallery.filter((slide) => slide.habilitado === 1),
        });
      })
    );
  }

  getDashboardStats(): Observable<DashboardStats> {
    return this.http.get(this.statsUrl, { withCredentials: true, responseType: 'text' }).pipe(
      map((rawResponse) => {
        const response = this.parseApiResponse<DashboardStats>(rawResponse);
        if (!response.success || !response.data) {
          throw new Error(response.message || 'No fue posible obtener las estadisticas del dashboard');
        }
        return response.data;
      })
    );
  }

  private parseApiResponse<T>(rawResponse: unknown): SiteCustomizationResponse<T> {
    if (typeof rawResponse !== 'string') {
      return rawResponse as SiteCustomizationResponse<T>;
    }

    const text = rawResponse.replace(/^\uFEFF/, '').trim();
    if (!text) {
      throw new Error('El servidor devolvio una respuesta vacia');
    }

    if (this.looksLikeInfinityFreeChallenge(text)) {
      throw new Error('El hosting devolvio una pagina de verificacion (InfinityFree) en lugar de JSON');
    }

    const parsedDirect = this.tryParseJson<SiteCustomizationResponse<T>>(text);
    if (parsedDirect !== null) {
      return parsedDirect;
    }

    const preJson = this.extractJsonFromPreTag(text);
    if (preJson) {
      const parsedFromPre = this.tryParseJson<SiteCustomizationResponse<T>>(preJson);
      if (parsedFromPre !== null) {
        return parsedFromPre;
      }
    }

    const start = text.indexOf('{');
    const end = text.lastIndexOf('}');
    if (start !== -1 && end > start) {
      const candidate = text.slice(start, end + 1);
      const parsedCandidate = this.tryParseJson<SiteCustomizationResponse<T>>(candidate);
      if (parsedCandidate !== null) {
        return parsedCandidate;
      }
    }

    throw new Error('El servidor devolvio una respuesta invalida');
  }

  private extractJsonFromPreTag(text: string): string | null {
    const match = text.match(/<pre[^>]*>([\s\S]*?)<\/pre>/i);
    if (!match || !match[1]) {
      return null;
    }

    return match[1]
      .replace(/&quot;/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .trim();
  }

  private looksLikeInfinityFreeChallenge(text: string): boolean {
    const lower = text.toLowerCase();
    return lower.includes('/aes.js') || lower.includes('tonumbers(') || lower.includes('openresty');
  }

  private tryParseJson<T>(text: string): T | null {
    try {
      return JSON.parse(text) as T;
    } catch {
      return null;
    }
  }
}