import { ChangeDetectionStrategy, Component, OnInit, computed, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin } from 'rxjs';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { Router } from '@angular/router';
import {
  DashboardStats,
  SiteCarouselItem,
  SiteCustomizationConfig,
  SiteCustomizationSavePayload,
} from '../../../shared/models/site-customization.model';
import { AuthService } from '../../../shared/services/auth.service';
import { SiteCustomizationService } from '../../../shared/services/site-customization.service';

type DashboardSection = 'navbar' | 'sidebar' | 'carousel' | 'shop_carousel' | 'shop_gallery';
type SlideCollection = 'carousel' | 'shop_carousel' | 'shop_gallery';

interface EditableCarouselSlide {
  id_carrousel: number | null;
  client_key: string;
  titulo: string;
  descripcion: string;
  enlace: string;
  orden_visual: number;
  habilitado: boolean;
  foto_perfil_url: string | null;
  foto_perfil_public_id: string | null;
  preview_url: string | null;
  remove_image: boolean;
  file: File | null;
}

@Component({
  selector: 'app-admin-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatButtonModule,
    MatCardModule,
    MatFormFieldModule,
    MatIconModule,
    MatInputModule,
    MatProgressSpinnerModule,
    MatSlideToggleModule,
    MatSnackBarModule,
  ],
  templateUrl: './dashboard.html',
  styleUrl: './dashboard.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class AdminDashboard implements OnInit {
  private readonly siteCustomizationService = inject(SiteCustomizationService);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly snackBar = inject(MatSnackBar);
  private readonly fb = inject(FormBuilder);

  readonly loading = signal(true);
  readonly saving = signal(false);
  readonly selectedSection = signal<DashboardSection>('navbar');
  readonly stats = signal<DashboardStats | null>(null);
  readonly carouselSlides = signal<EditableCarouselSlide[]>([]);
  readonly shopCarouselSlides = signal<EditableCarouselSlide[]>([]);
  readonly shopGallerySlides = signal<EditableCarouselSlide[]>([]);
  readonly navbarLogoPreview = signal<string | null>(null);
  readonly navbarLogoFile = signal<File | null>(null);
  readonly removeNavbarLogo = signal(false);
  readonly sidebarLogoPreview = signal<string | null>(null);
  readonly sidebarLogoFile = signal<File | null>(null);
  readonly removeSidebarLogo = signal(false);

  readonly statsCards = computed(() => {
    const stats = this.stats();
    if (!stats) {
      return [];
    }

    return [
      { label: 'Usuarios registrados', value: stats.usuarios_registrados, icon: 'groups' },
      { label: 'Alumnos', value: stats.alumnos, icon: 'school' },
      { label: 'Administradores', value: stats.administradores, icon: 'admin_panel_settings' },
      { label: 'Pendientes', value: stats.pendientes_aprobacion, icon: 'hourglass_top' },
      { label: 'Instituciones', value: stats.instituciones, icon: 'apartment' },
      { label: 'Carreras', value: stats.carreras, icon: 'menu_book' },
    ];
  });

  readonly navbarForm = this.fb.group({
    brand_text: this.fb.nonNullable.control('', [Validators.maxLength(255)]),
  });

  readonly sidebarForm = this.fb.group({
    brand_text: this.fb.nonNullable.control('', [Validators.maxLength(255)]),
  });

  ngOnInit(): void {
    this.cargarDashboard();
  }

  seleccionarSeccion(section: DashboardSection): void {
    this.selectedSection.set(section);
  }

  agregarSlide(collection: SlideCollection): void {
    const current = this.getSlidesByCollection(collection);
    const slides = [...current];
    slides.push(this.createEmptySlide(slides.length + 1));
    this.setSlidesByCollection(collection, slides);
  }

  eliminarSlide(collection: SlideCollection, clientKey: string): void {
    const slides = this.getSlidesByCollection(collection).filter((slide) => slide.client_key !== clientKey);
    this.setSlidesByCollection(collection, this.reordenarSlides(slides));
  }

  actualizarSlide(collection: SlideCollection, clientKey: string, field: keyof EditableCarouselSlide, value: string | boolean | null): void {
    const slides = this.getSlidesByCollection(collection).map((slide) => {
      if (slide.client_key !== clientKey) {
        return slide;
      }

      return {
        ...slide,
        [field]: value,
      };
    });

    this.setSlidesByCollection(collection, slides);
  }

  cambiarArchivoNavbar(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    this.navbarLogoFile.set(file);
    this.removeNavbarLogo.set(false);
    this.navbarLogoPreview.set(file ? URL.createObjectURL(file) : this.navbarLogoPreview());
  }

  quitarLogoNavbar(): void {
    this.navbarLogoFile.set(null);
    this.navbarLogoPreview.set(null);
    this.removeNavbarLogo.set(true);
  }

  cambiarArchivoSidebar(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    this.sidebarLogoFile.set(file);
    this.removeSidebarLogo.set(false);
    this.sidebarLogoPreview.set(file ? URL.createObjectURL(file) : this.sidebarLogoPreview());
  }

  quitarLogoSidebar(): void {
    this.sidebarLogoFile.set(null);
    this.sidebarLogoPreview.set(null);
    this.removeSidebarLogo.set(true);
  }

  cambiarImagenSlide(collection: SlideCollection, clientKey: string, event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (!file) {
      return;
    }

    const slides = this.getSlidesByCollection(collection).map((slide) => {
      if (slide.client_key !== clientKey) {
        return slide;
      }

      return {
        ...slide,
        file,
        preview_url: URL.createObjectURL(file),
        remove_image: false,
      };
    });

    this.setSlidesByCollection(collection, slides);
  }

  quitarImagenSlide(collection: SlideCollection, clientKey: string): void {
    const slides = this.getSlidesByCollection(collection).map((slide) => {
      if (slide.client_key !== clientKey) {
        return slide;
      }

      return {
        ...slide,
        file: null,
        preview_url: null,
        foto_perfil_url: null,
        foto_perfil_public_id: null,
        remove_image: true,
      };
    });

    this.setSlidesByCollection(collection, slides);
  }

  guardarCambios(): void {
    if (this.navbarForm.invalid || this.sidebarForm.invalid) {
      this.navbarForm.markAllAsTouched();
      this.sidebarForm.markAllAsTouched();
      this.mostrarMensaje('El texto del navbar o sidebar supera el maximo permitido', 'error');
      return;
    }

    const payload: SiteCustomizationSavePayload = {
      navbar: {
        brand_text: this.navbarForm.getRawValue().brand_text.trim(),
        remove_logo: this.removeNavbarLogo(),
        logo_selected: !!this.navbarLogoFile(),
      },
      sidebar: {
        brand_text: this.sidebarForm.getRawValue().brand_text.trim(),
        remove_logo: this.removeSidebarLogo(),
        logo_selected: !!this.sidebarLogoFile(),
      },
      carousel: this.reordenarSlides(this.carouselSlides()).map((slide, index) => ({
        id_carrousel: slide.id_carrousel,
        client_key: slide.client_key,
        titulo: slide.titulo.trim(),
        descripcion: slide.descripcion.trim(),
        enlace: slide.enlace.trim(),
        orden_visual: index + 1,
        habilitado: slide.habilitado,
        remove_image: slide.remove_image,
      })),
      shop_carousel: this.reordenarSlides(this.shopCarouselSlides()).map((slide, index) => ({
        id_carrousel: slide.id_carrousel,
        client_key: slide.client_key,
        titulo: slide.titulo.trim(),
        descripcion: slide.descripcion.trim(),
        enlace: slide.enlace.trim(),
        orden_visual: index + 1,
        habilitado: slide.habilitado,
        remove_image: slide.remove_image,
      })),
      shop_gallery: this.reordenarSlides(this.shopGallerySlides()).map((slide, index) => ({
        id_carrousel: slide.id_carrousel,
        client_key: slide.client_key,
        titulo: slide.titulo.trim(),
        descripcion: slide.descripcion.trim(),
        enlace: slide.enlace.trim(),
        orden_visual: index + 1,
        habilitado: slide.habilitado,
        remove_image: slide.remove_image,
      })),
    };

    const carouselFiles = this.carouselSlides().reduce<Record<string, File | null>>((acc, slide) => {
      acc[slide.client_key] = slide.file;
      return acc;
    }, {});

    const shopCarouselFiles = this.shopCarouselSlides().reduce<Record<string, File | null>>((acc, slide) => {
      acc[slide.client_key] = slide.file;
      return acc;
    }, {});

    const shopGalleryFiles = this.shopGallerySlides().reduce<Record<string, File | null>>((acc, slide) => {
      acc[slide.client_key] = slide.file;
      return acc;
    }, {});

    this.saving.set(true);

    this.siteCustomizationService.saveSiteConfig(payload, {
      navbarLogo: this.navbarLogoFile(),
      sidebarLogo: this.sidebarLogoFile(),
      carouselFiles,
      shopCarouselFiles,
      shopGalleryFiles,
    }).subscribe({
      next: (config) => {
        this.applyConfig(config);
        this.saving.set(false);
        this.mostrarMensaje('Personalizacion guardada correctamente', 'success');
      },
      error: (error) => {
        this.saving.set(false);

        if (this.handleAuthErrors(error)) {
          return;
        }

        this.mostrarMensaje(error?.message || 'No fue posible guardar la personalizacion', 'error');
      },
    });
  }

  private cargarDashboard(): void {
    this.loading.set(true);

    forkJoin({
      config: this.siteCustomizationService.getAdminConfig(),
      stats: this.siteCustomizationService.getDashboardStats(),
    }).subscribe({
      next: ({ config, stats }) => {
        this.applyConfig(config);
        this.stats.set(stats);
        this.loading.set(false);
      },
      error: (error) => {
        this.loading.set(false);

        if (this.handleAuthErrors(error)) {
          return;
        }

        this.mostrarMensaje(error?.message || 'No fue posible cargar el dashboard', 'error');
      },
    });
  }

  private handleAuthErrors(error: unknown): boolean {
    const status = Number((error as { status?: number })?.status ?? 0);

    if (status === 401) {
      this.authService.logout().subscribe({
        next: () => {
          this.mostrarMensaje('Tu sesion expiro. Inicia sesion nuevamente.', 'error');
          void this.router.navigate(['/home']);
        },
        error: () => {
          this.mostrarMensaje('Tu sesion expiro. Inicia sesion nuevamente.', 'error');
          void this.router.navigate(['/home']);
        },
      });

      return true;
    }

    if (status === 403) {
      this.mostrarMensaje('No tienes permisos para acceder al dashboard.', 'error');
      void this.router.navigate(['/home']);
      return true;
    }

    return false;
  }

  private applyConfig(config: SiteCustomizationConfig): void {
    this.navbarForm.patchValue({
      brand_text: config.navbar.brand_text ?? '',
    });

    this.sidebarForm.patchValue({
      brand_text: config.sidebar.brand_text ?? '',
    });

    this.navbarLogoPreview.set(config.navbar.logo_url || null);
    this.navbarLogoFile.set(null);
    this.removeNavbarLogo.set(false);
    this.sidebarLogoPreview.set(config.sidebar.logo_url || null);
    this.sidebarLogoFile.set(null);
    this.removeSidebarLogo.set(false);
    this.carouselSlides.set(this.reordenarSlides((config.carousel ?? []).map((slide, index) => this.mapEditableSlide(slide, index))));
    this.shopCarouselSlides.set(this.reordenarSlides((config.shop_carousel ?? []).map((slide, index) => this.mapEditableSlide(slide, index, 'shop_carousel'))));
    this.shopGallerySlides.set(this.reordenarSlides((config.shop_gallery ?? []).map((slide, index) => this.mapEditableSlide(slide, index, 'shop_gallery'))));
  }

  private mapEditableSlide(slide: SiteCarouselItem, index: number, prefix = 'slide'): EditableCarouselSlide {
    return {
      id_carrousel: slide.id_carrousel,
      client_key: `${prefix}_${slide.id_carrousel}_${index}`,
      titulo: slide.titulo,
      descripcion: slide.descripcion,
      enlace: slide.enlace ?? '',
      orden_visual: slide.orden_visual || index + 1,
      habilitado: slide.habilitado === 1,
      foto_perfil_url: slide.foto_perfil_url,
      foto_perfil_public_id: slide.foto_perfil_public_id,
      preview_url: slide.foto_perfil_url,
      remove_image: false,
      file: null,
    };
  }

  private createEmptySlide(order: number): EditableCarouselSlide {
    return {
      id_carrousel: null,
      client_key: `new_${Date.now()}_${order}`,
      titulo: '',
      descripcion: '',
      enlace: '',
      orden_visual: order,
      habilitado: true,
      foto_perfil_url: null,
      foto_perfil_public_id: null,
      preview_url: null,
      remove_image: false,
      file: null,
    };
  }

  private reordenarSlides(slides: EditableCarouselSlide[]): EditableCarouselSlide[] {
    return slides.map((slide, index) => ({
      ...slide,
      orden_visual: index + 1,
    }));
  }

  private getSlidesByCollection(collection: SlideCollection): EditableCarouselSlide[] {
    if (collection === 'shop_carousel') {
      return this.shopCarouselSlides();
    }

    if (collection === 'shop_gallery') {
      return this.shopGallerySlides();
    }

    return this.carouselSlides();
  }

  private setSlidesByCollection(collection: SlideCollection, slides: EditableCarouselSlide[]): void {
    if (collection === 'shop_carousel') {
      this.shopCarouselSlides.set(slides);
      return;
    }

    if (collection === 'shop_gallery') {
      this.shopGallerySlides.set(slides);
      return;
    }

    this.carouselSlides.set(slides);
  }

  private mostrarMensaje(mensaje: string, tipo: 'success' | 'error'): void {
    this.snackBar.open(mensaje, 'Cerrar', {
      duration: 3500,
      horizontalPosition: 'center',
      verticalPosition: 'top',
      panelClass: tipo === 'success' ? 'snackbar-success' : 'snackbar-error',
    });
  }
}