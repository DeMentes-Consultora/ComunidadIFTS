import { Component, OnInit, OnDestroy, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { interval, Subscription } from 'rxjs';
import { SiteCustomizationService } from '../../services/site-customization.service';
import { SiteCarouselItem } from '../../models/site-customization.model';

interface Slide {
  id: number | string;
  titulo: string;
  descripcion: string;
  imagen: string | null;
  enlace?: string;
}

@Component({
  selector: 'app-carrusel',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule],
  templateUrl: './carrusel.html',
  styleUrls: ['./carrusel.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class CarruselComponent implements OnInit, OnDestroy {
  slides: Slide[] = [
    {
      id: 'fallback-1',
      titulo: 'Bienvenido a Comunidad IFTS',
      descripcion: 'Conecta con todos los Institutos Superiores de Tecnología de Buenos Aires',
      imagen: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      enlace: '#'
    },
    {
      id: 'fallback-2',
      titulo: 'IFTS y comunidad',
      descripcion: 'Descubre instituciones, carreras y oportunidades en un solo lugar',
      imagen: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
      enlace: '#'
    },
    {
      id: 'fallback-3',
      titulo: 'Únete a la comunidad',
      descripcion: 'Personaliza el sitio y mantén la información siempre actualizada desde el dashboard',
      imagen: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      enlace: '#'
    }
  ];

  currentIndex = 0;
  private autoPlaySubscription: Subscription | null = null;

  constructor(
    private cdr: ChangeDetectorRef,
    private siteCustomizationService: SiteCustomizationService
  ) {}

  ngOnInit(): void {
    this.siteCustomizationService.loadPublicConfig().subscribe({
      next: (config) => {
        const slidesConfigurados = (config.carousel ?? []).filter(slide => slide.habilitado === 1);
        if (slidesConfigurados.length > 0) {
          this.slides = slidesConfigurados.map((slide) => this.mapSlide(slide));
          this.currentIndex = 0;
          this.cdr.markForCheck();
        }
      }
    });
    this.startAutoPlay();
  }

  ngOnDestroy(): void {
    this.stopAutoPlay();
  }

  private startAutoPlay(): void {
    this.autoPlaySubscription = interval(5000).subscribe(() => {
      this.nextSlide();
    });
  }

  private stopAutoPlay(): void {
    if (this.autoPlaySubscription) {
      this.autoPlaySubscription.unsubscribe();
    }
  }

  nextSlide(): void {
    if (this.slides.length === 0) {
      return;
    }

    this.currentIndex = (this.currentIndex + 1) % this.slides.length;
    this.cdr.markForCheck();
  }

  prevSlide(): void {
    if (this.slides.length === 0) {
      return;
    }

    this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
    this.cdr.markForCheck();
  }

  goToSlide(index: number): void {
    if (this.slides.length === 0) {
      return;
    }

    this.currentIndex = index;
    this.cdr.markForCheck();
    // Reiniciar el autoplay cuando el usuario interactúa
    this.stopAutoPlay();
    this.startAutoPlay();
  }

  get currentSlide(): Slide {
    return this.slides[this.currentIndex];
  }

  getCurrentSlideBackground(slide: Slide): string | null {
    if (!slide.imagen) {
      return null;
    }

    if (slide.imagen.startsWith('http://') || slide.imagen.startsWith('https://')) {
      return `url('${slide.imagen}') center / cover no-repeat`;
    }

    return slide.imagen;
  }

  private mapSlide(slide: SiteCarouselItem): Slide {
    return {
      id: slide.id_carrousel,
      titulo: slide.titulo || 'Comunidad IFTS',
      descripcion: slide.descripcion || '',
      imagen: slide.foto_perfil_url || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      enlace: slide.enlace || '#'
    };
  }
}
