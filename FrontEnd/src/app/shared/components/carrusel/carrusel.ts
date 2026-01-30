import { Component, OnInit, OnDestroy, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { interval, Subscription } from 'rxjs';

interface Slide {
  id: number;
  titulo: string;
  descripcion: string;
  imagen: string;
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
      id: 1,
      titulo: 'Bienvenido a Comunidad IFTS',
      descripcion: 'Conecta con todos los Institutos Superiores de Tecnología de Buenos Aires',
      imagen: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      enlace: '#'
    },
    {
      id: 2,
      titulo: 'IFTS 12 - Análisis de Sistemas',
      descripcion: 'Forma parte de los mejores programas de tecnología',
      imagen: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
      enlace: '#'
    },
    {
      id: 3,
      titulo: 'IFTS 20 - Gestión de Redes',
      descripcion: 'Especialízate en infraestructura tecnológica',
      imagen: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      enlace: '#'
    },
    {
      id: 4,
      titulo: 'IFTS 15 - Desarrollo Web',
      descripcion: 'Aprende las últimas tecnologías en desarrollo web',
      imagen: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
      enlace: '#'
    },
    {
      id: 5,
      titulo: 'Únete a la comunidad',
      descripcion: 'Descubre oportunidades de aprendizaje y crecimiento profesional',
      imagen: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
      enlace: '#'
    }
  ];

  currentIndex = 0;
  private autoPlaySubscription: Subscription | null = null;

  constructor(private cdr: ChangeDetectorRef) {}

  ngOnInit(): void {
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
    this.currentIndex = (this.currentIndex + 1) % this.slides.length;
    this.cdr.markForCheck();
  }

  prevSlide(): void {
    this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
    this.cdr.markForCheck();
  }

  goToSlide(index: number): void {
    this.currentIndex = index;
    this.cdr.markForCheck();
    // Reiniciar el autoplay cuando el usuario interactúa
    this.stopAutoPlay();
    this.startAutoPlay();
  }

  get currentSlide(): Slide {
    return this.slides[this.currentIndex];
  }
}
