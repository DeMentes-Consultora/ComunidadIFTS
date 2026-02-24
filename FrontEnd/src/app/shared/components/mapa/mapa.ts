import { Component, AfterViewInit, OnDestroy, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as L from 'leaflet';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { InstitucionesService } from '../../services/instituciones.service';
import { MarkerClusterService } from '../../services/marker-cluster.service';
import { Institucion } from '../../models/institucion.model';
import { BuscadorDireccionComponent } from '../buscador-direccion/buscador-direccion';
import { FormularioInstitucionComponent } from '../formulario-institucion/formulario-institucion';

@Component({
  selector: 'app-mapa',
  standalone: true,
  imports: [CommonModule, BuscadorDireccionComponent, MatDialogModule],
  templateUrl: './mapa.html',
  styleUrls: ['./mapa.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class MapaComponent implements OnInit, AfterViewInit, OnDestroy {
  map: L.Map | null = null;
  private instituciones: Institucion[] = [];
  institucionesParaBusqueda: Institucion[] = [];
  private isLoading = true;
  private useClustering = false;
  private popupCloseTimers = new Map<number, ReturnType<typeof setTimeout>>();
  private markersByInstitucionId = new Map<number, L.CircleMarker>();
  private renderedMarkers: L.CircleMarker[] = [];

  constructor(
    private institucionesService: InstitucionesService,
    private markerClusterService: MarkerClusterService,
    private dialog: MatDialog,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.cargarInstituciones();
  }

  ngAfterViewInit(): void {
    this.initMap();
  }

  ngOnDestroy(): void {
    this.popupCloseTimers.forEach((timer) => clearTimeout(timer));
    this.popupCloseTimers.clear();
    this.markerClusterService.destroy();
    this.map?.remove();
  }

  onDireccionEncontrada(evento: { coordenadas: L.LatLng; direccion: string }): void {
    // Abrir formulario de registro de institución
    const dialogRef = this.dialog.open(FormularioInstitucionComponent, {
      width: '90%',
      maxWidth: '600px',
      data: { coordenadas: evento.coordenadas, direccion: evento.direccion }
    });

    dialogRef.componentInstance.coordenadas = evento.coordenadas;
    dialogRef.componentInstance.direccion = evento.direccion;

    dialogRef.afterClosed().subscribe((resultado) => {
      if (resultado) {
        // Recargar instituciones después de guardar
        this.cargarInstituciones();
      }
    });
  }

  onInstitucionSeleccionada(idInstitucion: number): void {
    const marker = this.markersByInstitucionId.get(idInstitucion);
    if (!marker || !this.map) {
      return;
    }

    const abrirPopup = () => {
      this.clearCloseTimer(idInstitucion);
      marker.openPopup();
    };

    if (this.useClustering) {
      const clusterGroup = this.markerClusterService.getClusterGroup();
      if (clusterGroup && typeof clusterGroup.zoomToShowLayer === 'function') {
        clusterGroup.zoomToShowLayer(marker, abrirPopup);
        return;
      }
    }

    this.map.panTo(marker.getLatLng());
    abrirPopup();
  }

  private cargarInstituciones(): void {
    this.isLoading = true;
    this.institucionesService.obtenerTodas().subscribe({
      next: (instituciones) => {
        this.instituciones = instituciones;
        this.institucionesParaBusqueda = instituciones;
        this.isLoading = false;
        if (this.map) {
          this.renderInstituciones();
        }
      },
      error: (error) => {
        console.error('Error al cargar instituciones:', error);
        this.isLoading = false;
      }
    });
  }

  private async initMap(): Promise<void> {
    // En móvil usamos zoom 11, en desktop 13
    const initialZoom = window.innerWidth < 768 ? 11 : 13;

    this.map = L.map('mapa-ifts', {
      zoomControl: true
    }).setView([-34.6037, -58.3816], initialZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(this.map);

    // ✨ INICIALIZAR CLUSTERING DE MARCADORES (con fallback)
    const clusterGroup = await this.markerClusterService.initClusterGroup(this.map, {
      maxClusterRadius: 80,
      disableClusteringAtZoom: 18
    });
    this.useClustering = !!clusterGroup;
    if (!this.useClustering) {
      console.warn('Clustering no disponible: se renderizarán marcadores directos en el mapa');
    }

    // Notificar a Angular que el mapa ha sido inicializado
    this.cdr.markForCheck();

    // Si ya hay instituciones cargadas, renderizarlas
    if (this.instituciones.length > 0) {
      this.renderInstituciones();
    }
  }

  private renderInstituciones(): void {
    if (!this.map) return;

    this.markersByInstitucionId.clear();

    if (this.useClustering) {
      this.markerClusterService.clearAllMarkers();
    } else {
      this.renderedMarkers.forEach((marker) => marker.remove());
    }
    this.renderedMarkers = [];

    // ✨ AGREGAR MARCADORES AL CLUSTER EN LUGAR DE AL MAPA
    this.instituciones.forEach((inst) => {
      const marker = L.circleMarker([inst.latitud, inst.longitud], {
        radius: 10,
        color: '#006633',
        fillColor: '#66bb6a',
        fillOpacity: 0.9,
        weight: 2
      });

      marker.bindPopup(this.getPopupContent(inst), {
        closeButton: false,
        autoClose: true,
        closeOnClick: false,
        autoPan: true,
        keepInView: true,
        maxWidth: 420,
        minWidth: 320,
        className: 'ifts-hover-popup'
      });

      marker.on('mouseover', () => {
        this.clearCloseTimer(inst.id);
        marker.openPopup();
      });

      marker.on('mouseout', () => {
        this.scheduleClose(inst.id, marker);
      });

      marker.on('popupopen', () => {
        const popupElement = marker.getPopup()?.getElement();
        if (!popupElement) {
          return;
        }

        popupElement.addEventListener('mouseenter', () => {
          this.clearCloseTimer(inst.id);
        });

        popupElement.addEventListener('mouseleave', () => {
          this.scheduleClose(inst.id, marker);
        });
      });

      if (this.useClustering) {
        this.markerClusterService.addMarker(marker);
      } else {
        marker.addTo(this.map!);
      }

      this.markersByInstitucionId.set(inst.id, marker);
      this.renderedMarkers.push(marker);
    });

    // Log de información
    if (this.useClustering) {
      console.log(`✨ ${this.markerClusterService.getMarkerCount()} instituciones cargadas en clustering`);
    } else {
      console.log(`📍 ${this.instituciones.length} instituciones cargadas sin clustering`);
    }
  }

  private getPopupContent(inst: Institucion): string {
    const nombre = this.escapeHtml(inst.nombre);
    const logo = inst.logo
      ? `<img src="${this.escapeHtml(inst.logo)}" alt="Logo ${nombre}" style="width:56px;height:56px;object-fit:contain;border-radius:8px;border:1px solid #e5e7eb;background:#fff;" />`
      : '';

    const direccion = this.escapeHtml(inst.direccion || 'No informado');
    const telefono = this.escapeHtml(inst.telefono || 'No informado');
    const email = inst.email
      ? `<a href="mailto:${this.escapeHtml(inst.email)}" style="color:#1d4ed8;text-decoration:none;">${this.escapeHtml(inst.email)}</a>`
      : 'No informado';
    const web = inst.sitio_web
      ? `<a href="${this.escapeHtml(inst.sitio_web)}" target="_blank" rel="noopener noreferrer" style="color:#1d4ed8;text-decoration:none;">${this.escapeHtml(inst.sitio_web)}</a>`
      : 'No informado';
    const carreras = this.formatCarreras(inst.carreras as unknown[] | undefined);
    const observaciones = this.formatObservaciones(inst.observaciones);

    const hasLike = Number(inst.likes) === 1;
    const heartIcon = hasLike ? '♥' : '♡';
    const heartText = hasLike ? 'Con like' : 'Sin like';

    return `
      <div style="min-width:300px;max-width:360px;font-family:Arial,sans-serif;color:#111827;line-height:1.35;">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:8px;">
          <h3 style="margin:0;font-size:18px;color:#111827;">${nombre}</h3>
          ${logo}
        </div>

        <div style="display:flex;flex-direction:column;gap:4px;font-size:13px;margin-bottom:10px;">
          <div><strong>Dirección:</strong> ${direccion}</div>
          <div><strong>Teléfono:</strong> ${telefono}</div>
          <div><strong>Mail:</strong> ${email}</div>
          <div><strong>Web:</strong> ${web}</div>
          <div><strong>Carreras:</strong></div>
          <div style="margin-left:8px;display:flex;flex-direction:column;gap:2px;">${carreras}</div>
        </div>

        <div style="margin-bottom:10px;font-size:13px;">
          <strong>Observaciones:</strong> ${observaciones}
        </div>

        <div style="display:flex;align-items:center;gap:8px;font-size:14px;color:#dc2626;font-weight:600;">
          <span style="font-size:20px;line-height:1;">${heartIcon}</span>
          <span>${heartText}</span>
        </div>
      </div>
    `;
  }

  private scheduleClose(id: number, marker: L.CircleMarker): void {
    this.clearCloseTimer(id);
    const timer = setTimeout(() => {
      marker.closePopup();
      this.popupCloseTimers.delete(id);
    }, 220);
    this.popupCloseTimers.set(id, timer);
  }

  private clearCloseTimer(id: number): void {
    const timer = this.popupCloseTimers.get(id);
    if (timer) {
      clearTimeout(timer);
      this.popupCloseTimers.delete(id);
    }
  }

  private formatCarreras(carreras: unknown[] | undefined): string {
    if (!Array.isArray(carreras) || carreras.length === 0) {
      return '<div>No informado</div>';
    }

    const nombres = carreras
      .map((carrera) => {
        if (typeof carrera === 'string') {
          return carrera;
        }

        if (carrera && typeof carrera === 'object' && 'nombre' in carrera) {
          const nombre = (carrera as { nombre?: unknown }).nombre;
          return typeof nombre === 'string' ? nombre : '';
        }

        return '';
      })
      .map((nombre) => nombre.trim())
      .filter((nombre) => nombre.length > 0);

    if (nombres.length === 0) {
      return '<div>No informado</div>';
    }

    return nombres
      .map((nombre) => `<div>• ${this.escapeHtml(nombre)}</div>`)
      .join('');
  }

  private formatObservaciones(observaciones: string | null | undefined): string {
    const limpio = (observaciones ?? '').replace(/\r\n/g, '\n').trim();

    if (!limpio || limpio.replace(/[\s,]/g, '') === '') {
      return 'Sin observaciones';
    }

    return this.escapeHtml(limpio).replace(/\n/g, '<br>');
  }

  private escapeHtml(value: string): string {
    return value
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
}
