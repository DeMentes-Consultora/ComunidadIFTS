import { Component, AfterViewInit, OnDestroy, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as L from 'leaflet';
import { InstitucionesService } from '../../services/instituciones.service';
import { Institucion } from '../../models/institucion.model';

@Component({
  selector: 'app-mapa',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './mapa.html',
  styleUrls: ['./mapa.css']
})
export class MapaComponent implements OnInit, AfterViewInit, OnDestroy {
  private map: L.Map | null = null;
  private instituciones: Institucion[] = [];
  private isLoading = true;

  constructor(private institucionesService: InstitucionesService) {}

  ngOnInit(): void {
    this.cargarInstituciones();
  }

  ngAfterViewInit(): void {
    this.initMap();
  }

  ngOnDestroy(): void {
    this.map?.remove();
  }

  private cargarInstituciones(): void {
    this.isLoading = true;
    this.institucionesService.obtenerTodas().subscribe({
      next: (instituciones) => {
        this.instituciones = instituciones;
        this.isLoading = false;
        // Si el mapa ya está inicializado, renderizar instituciones
        if (this.map) {
          this.renderInstituciones();
        }
      },
      error: (error) => {
        console.error('Error al cargar instituciones:', error);
        this.isLoading = false;
        // Opcionalmente mostrar mensaje de error al usuario
      }
    });
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

    // Si ya hay instituciones cargadas, renderizarlas
    if (this.instituciones.length > 0) {
      this.renderInstituciones();
    }
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
