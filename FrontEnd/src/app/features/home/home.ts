import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';

interface Carrera {
  id: number;
  nombre: string;
  descripcion?: string;
}

interface Institucion {
  id: number;
  nombre: string;
  direccion: string;
  latitud?: number;
  longitud?: number;
  telefono?: string;
  email?: string;
  sitio_web?: string;
  logo_base64?: string;
  descripcion?: string;
  carreras?: Carrera[];
  likes?: number;
}

@Component({
  selector: 'app-home',
  imports: [CommonModule],
  templateUrl: './home.html',
  styleUrl: './home.css'
})
export class Home implements OnInit {
  instituciones: Institucion[] = [];
  totalInstituciones: number = 0;
  totalCarreras: number = 0;

  ngOnInit(): void {
    this.cargarInstituciones();
  }

  cargarInstituciones(): void {
    // TODO: Reemplazar con servicio HTTP real
    // Por ahora, datos de prueba
    this.instituciones = [
      {
        id: 1,
        nombre: 'IFTS Nº 12',
        direccion: 'Av. Callao 753, CABA',
        telefono: '011-4813-8400',
        email: 'ifts12@bue.edu.ar',
        sitio_web: 'https://ifts12.edu.ar',
        latitud: -34.6037,
        longitud: -58.3816,
        carreras: [
          { id: 1, nombre: 'Desarrollo de Software' },
          { id: 2, nombre: 'Análisis de Sistemas' }
        ],
        likes: 45
      },
      {
        id: 2,
        nombre: 'IFTS Nº 20',
        direccion: 'Av. Nazca 2546, CABA',
        telefono: '011-4502-5200',
        email: 'ifts20@bue.edu.ar',
        sitio_web: 'https://ifts20.edu.ar',
        latitud: -34.6140,
        longitud: -58.4700,
        carreras: [
          { id: 3, nombre: 'Tecnología de la Información' },
          { id: 4, nombre: 'Redes y Telecomunicaciones' }
        ],
        likes: 32
      }
    ];
    
    this.totalInstituciones = this.instituciones.length;
    this.totalCarreras = this.instituciones.reduce(
      (total, inst) => total + (inst.carreras?.length || 0), 
      0
    );
  }

  getInitials(nombre: string): string {
    return nombre
      .split(' ')
      .filter(word => word.length > 0)
      .slice(0, 2)
      .map(word => word[0].toUpperCase())
      .join('');
  }

  darLike(institucionId: number): void {
    const institucion = this.instituciones.find(i => i.id === institucionId);
    if (institucion) {
      institucion.likes = (institucion.likes || 0) + 1;
      // TODO: Llamar al servicio para persistir el like
      console.log(`Like agregado a institución ${institucionId}`);
    }
  }
}
