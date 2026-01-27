import { Component, OnInit, OnDestroy } from '@angular/core';
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
  styleUrls: ['./carrusel.css']
})
export class CarruselComponent implements OnInit, OnDestroy {
  slides: Slide[] = [
    {
      id: 1,
      titulo: 'Bienvenido a Comunidad IFTS',
      descripcion: 'Conecta con todos los Institutos Superiores de Tecnología de Buenos Aires',
      imagen: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1200&h=400&fit=crop',
      enlace: '#'
    },
    {
      id: 2,
      titulo: 'IFTS 12 - Análisis de Sistemas',
      descripcion: 'Forma parte de los mejores programas de tecnología',
      imagen: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1200&h=400&fit=crop',
      enlace: '#'
    },
    {
      id: 3,
      titulo: 'IFTS 20 - Gestión de Redes',
      descripcion: 'Especialízate en infraestructura tecnológica',
      imagen: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=1200&h=400&fit=crop',
      enlace: '#'
    },
    {
      id: 4,
      titulo: 'IFTS 15 - Desarrollo Web',
      descripcion: 'Aprende las últimas tecnologías en desarrollo web',
      imagen: 'https://images.unsplash.com/photo-1517694712529-c74f6c718a20?w=1200&h=400&fit=crop',
      enlace: '#'
    },
    {
      id: 5,
      titulo: 'Únete a la comunidad',
      descripcion: 'Descubre oportunidades de aprendizaje y crecimiento profesional',
      imagen: 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=1200&h=400&fit=crop',
      enlace: '#'
    }
  ];

  currentIndex = 0;
  private autoPlaySubscription: Subscription | null = null;

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
  }

  prevSlide(): void {
    this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
  }

  goToSlide(index: number): void {
    this.currentIndex = index;
    // Reiniciar el autoplay cuando el usuario interactúa
    this.stopAutoPlay();
    this.startAutoPlay();
  }

  get currentSlide(): Slide {
    return this.slides[this.currentIndex];
  }
}
