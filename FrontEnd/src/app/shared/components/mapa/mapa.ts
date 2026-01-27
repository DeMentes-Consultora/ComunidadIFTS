import { Component, AfterViewInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as L from 'leaflet';

interface Institucion {
  id: number;
  nombre: string;
  direccion?: string;
  latitud: number;
  longitud: number;
  telefono?: string;
  email?: string;
  sitio_web?: string;
  observaciones?: string;
  likes?: number;
}

@Component({
  selector: 'app-mapa',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './mapa.html',
  styleUrls: ['./mapa.css']
})
export class MapaComponent implements AfterViewInit, OnDestroy {
  private map: L.Map | null = null;

  // TODO: Reemplazar por servicio HTTP hacia BackEnd/api/instituciones.php
  private instituciones: Institucion[] = [
    {
      id: 1,
      nombre: 'IFTS 12',
      direccion: 'Av. Belgrano 637, CABA',
      latitud: -34.6125263,
      longitud: -58.37540925,
      telefono: '011 4345-6676',
      sitio_web: 'https://ifts12online.com.ar/',
      likes: 0
    },
    {
      id: 2,
      nombre: 'IFTS 20',
      direccion: 'Gurruchaga 739, CABA',
      latitud: -34.59542079,
      longitud: -58.43867534,
      telefono: '011 4776-0364',
      sitio_web: 'https://www.instagram.com/ifts_20/?hl=es',
      likes: 0
    },
    {
      id: 3,
      nombre: 'IFTS 15',
      direccion: 'Figueroa Alcorta 2977, CABA',
      latitud: -34.58072709,
      longitud: -58.39772877,
      telefono: '011 15-3898-1600',
      sitio_web: 'https://www.instagram.com/iftsn15?igsh=MWZrenltcm5yYnVwag==',
      likes: 0
    }
  ];

  ngAfterViewInit(): void {
    this.initMap();
    this.renderInstituciones();
  }

  ngOnDestroy(): void {
    this.map?.remove();
  }

  private initMap(): void {
    // En móvil usamos zoom 11, en desktop 13
    const initialZoom = window.innerWidth < 768 ? 11 : 13;

    this.map = L.map('mapa-ifts', {
      zoomControl: true
    }).setView([-34.6037, -58.3816], initialZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(this.map);
  }

  private renderInstituciones(): void {
    if (!this.map) return;

    this.instituciones.forEach((inst) => {
      const marker = L.circleMarker([inst.latitud, inst.longitud], {
        radius: 10,
        color: '#006633',
        fillColor: '#66bb6a',
        fillOpacity: 0.9,
        weight: 2
      }).addTo(this.map as L.Map);

      marker.bindPopup(this.getPopupContent(inst));
    });
  }

  private getPopupContent(inst: Institucion): string {
    const direccion = inst.direccion ? `<div><strong>Dirección:</strong> ${inst.direccion}</div>` : '';
    const telefono = inst.telefono ? `<div><strong>Teléfono:</strong> ${inst.telefono}</div>` : '';
    const web = inst.sitio_web ? `<div><a href="${inst.sitio_web}" target="_blank">Sitio web</a></div>` : '';

    return `
      <div class="popup">
        <h3>${inst.nombre}</h3>
        ${direccion}
        ${telefono}
        ${web}
      </div>
    `;
  }
}
