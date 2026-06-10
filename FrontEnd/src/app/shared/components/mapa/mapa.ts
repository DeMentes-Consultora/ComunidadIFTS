import { Component, AfterViewInit, OnDestroy, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as L from 'leaflet';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { InstitucionesService } from '../../services/instituciones.service';
import { MarkerClusterService } from '../../services/marker-cluster.service';
import { Institucion } from '../../models/institucion.model';
import { BuscadorDireccionComponent } from '../buscador-direccion/buscador-direccion';

@Component({
  selector: 'app-mapa',
  standalone: true,
  imports: [
    CommonModule, 
    BuscadorDireccionComponent, 
    MatIconModule,
    MatChipsModule
  ],
  templateUrl: './mapa.html',
  styleUrls: ['./mapa.css'],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class MapaComponent implements OnInit, AfterViewInit, OnDestroy {
  map: L.Map | null = null;
  private instituciones: Institucion[] = [];
  institucionesParaBusqueda: Institucion[] = [];
  institucionSeleccionada: Institucion | null = null;
  direccionBuscada: string | null = null;
  private isLoading = true;
  private useClustering = false;
  private popupCloseTimers = new Map<number, ReturnType<typeof setTimeout>>();
  private markersByInstitucionId = new Map<number, L.CircleMarker>();
  private renderedMarkers: L.CircleMarker[] = [];
  private carrerasFiltradasIds: number[] = [];
  private direccionMarker: L.Marker | null = null;
  private highlightedMarker: L.CircleMarker | null = null;

  get carrerasFiltradasActivas(): boolean {
    return this.carrerasFiltradasIds.length > 0;
  }

  constructor(
    private institucionesService: InstitucionesService,
    private markerClusterService: MarkerClusterService,
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
    this.direccionMarker?.remove();
    this.highlightedMarker?.closePopup();
    this.markerClusterService.destroy();
    this.map?.remove();
  }

  onInstitucionSeleccionada(idInstitucion: number): void {
    // Seleccionar la institución en el panel lateral
    const institucion = this.instituciones.find(i => i.id === idInstitucion);
    if (institucion) {
      this.limpiarDireccionBuscada();
      this.institucionSeleccionada = institucion;
      this.cdr.markForCheck();
    }

    // Animar el marcador en el mapa
    const marker = this.markersByInstitucionId.get(idInstitucion);
    if (!marker || !this.map) {
      return;
    }

    if (this.useClustering) {
      const clusterGroup = this.markerClusterService.getClusterGroup();
      if (clusterGroup && typeof clusterGroup.zoomToShowLayer === 'function') {
        clusterGroup.zoomToShowLayer(marker, () => {
          this.map?.panTo(marker.getLatLng());
          this.highlightMarker(marker);
        });
        return;
      }
    }

    this.map.panTo(marker.getLatLng());
    this.highlightMarker(marker);
  }

  onDireccionEncontrada(payload: { coordenadas: L.LatLng; direccion: string }): void {
    if (!this.map) {
      return;
    }

    this.institucionSeleccionada = null;
    this.direccionBuscada = payload.direccion;
    this.resetHighlightedMarker();

    this.direccionMarker?.remove();
    this.direccionMarker = L.marker(payload.coordenadas)
      .addTo(this.map)
      .bindPopup(`<strong>Ubicacion buscada</strong><br>${this.escapeHtml(payload.direccion)}`)
      .openPopup();

    this.map.setView(payload.coordenadas, Math.max(this.map.getZoom(), 16));
    this.cdr.markForCheck();
  }

  /**
   * Resaltar un marcador visualmente
   */
  private highlightMarker(marker: L.CircleMarker): void {
    if (this.highlightedMarker && this.highlightedMarker !== marker) {
      this.resetMarkerStyle(this.highlightedMarker);
      this.highlightedMarker.closePopup();
    }

    this.highlightedMarker = marker;
    marker.setStyle({
      radius: 15,
      color: '#14532d',
      fillColor: '#22c55e',
      fillOpacity: 1,
      weight: 4
    });
    marker.bringToFront();
    marker.openPopup();
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
        this.cdr.markForCheck();
      },
      error: (error) => {
        console.error('Error al cargar instituciones:', error);
        this.isLoading = false;
        this.cdr.markForCheck();
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

    // Limpiar marcadores existentes de manera segura
    this.limpiarMarcadores();

    // Obtener instituciones filtradas según carreras seleccionadas
    const institucionesMostrar = this.getInstitucionesFiltradas();

    // Agregar marcadores solo para las instituciones filtradas
    institucionesMostrar.forEach((inst) => {
      const marker = L.circleMarker([inst.latitud, inst.longitud], {
        radius: 10,
        color: '#006633',
        fillColor: '#66bb6a',
        fillOpacity: 0.9,
        weight: 2
      });

      // Click en el marcador: seleccionar institución en el panel
      marker.on('click', () => {
        this.onInstitucionSeleccionada(inst.id);
      });

      marker.bindPopup(this.buildInstitucionPopup(inst));

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
      console.log(`📍 ${institucionesMostrar.length} instituciones cargadas sin clustering`);
    }
  }

  private limpiarDireccionBuscada(): void {
    this.direccionBuscada = null;
    this.direccionMarker?.remove();
    this.direccionMarker = null;
  }

  /**
   * Limpiar marcadores de manera segura
   */
  private limpiarMarcadores(): void {
    // Limpiar el mapa del elemento _leaflet_id
    this.markersByInstitucionId.clear();
    this.highlightedMarker = null;

    if (this.useClustering) {
      // Para clustering: limpiar todos los layers del cluster
      this.markerClusterService.clearAllMarkers();
    } else {
      // Para marcadores individuales: remover cada uno del mapa
      this.renderedMarkers.forEach((marker) => {
        if (marker && this.map && this.map.hasLayer(marker)) {
          marker.remove();
        }
      });
    }
    
    // Limpiar el array de marcadores renderizados
    this.renderedMarkers = [];
  }

  /**
   * Obtener instituciones filtradas por carreras
   */
  private getInstitucionesFiltradas(): Institucion[] {
    // Si no hay filtro de carreras, retornar todas
    if (this.carrerasFiltradasIds.length === 0) {
      return this.instituciones;
    }

    // Filtrar instituciones que tienen al menos una de las carreras seleccionadas
    return this.instituciones.filter(inst => {
      if (!inst.carreras || inst.carreras.length === 0) return false;
      
      return inst.carreras.some(carrera => 
        this.carrerasFiltradasIds.includes(carrera.id)
      );
    });
  }

  /**
   * Manejar filtro de carreras desde el buscador
   */
  onCarrerasFiltradas(carrerasIds: number[]): void {
    this.carrerasFiltradasIds = carrerasIds;
    
    // Re-renderizar el mapa con el filtro aplicado
    this.renderInstituciones();

    if (carrerasIds.length === 0) {
      this.reencuadrarInstitucionesVisibles();
    }
    
    // Si hay carreras filtradas y había una institución seleccionada que ya no está visible, limpiar selección
    if (carrerasIds.length > 0 && this.institucionSeleccionada) {
      const sigueVisible = this.getInstitucionesFiltradas()
        .some(inst => inst.id === this.institucionSeleccionada?.id);
      
      if (!sigueVisible) {
        this.institucionSeleccionada = null;
        this.cdr.markForCheck();
      }
    }
    
    this.cdr.markForCheck();
  }

  isCarreraFiltrada(idCarrera: number): boolean {
    return this.carrerasFiltradasIds.includes(idCarrera);
  }

  toggleCarreraFiltro(idCarrera: number): void {
    const siguientes = this.isCarreraFiltrada(idCarrera)
      ? this.carrerasFiltradasIds.filter((id) => id !== idCarrera)
      : [...this.carrerasFiltradasIds, idCarrera];

    this.onCarrerasFiltradas(siguientes);
  }

  limpiarFiltroCarrerasPanel(): void {
    this.onCarrerasFiltradas([]);
  }

  private reencuadrarInstitucionesVisibles(): void {
    if (!this.map) {
      return;
    }

    if (this.useClustering) {
      this.markerClusterService.zoomToAllMarkers();
      return;
    }

    const bounds = L.latLngBounds(
      this.renderedMarkers.map((marker) => marker.getLatLng())
    );

    if (bounds.isValid()) {
      this.map.fitBounds(bounds, { padding: [50, 50] });
    }
  }

  private escapeHtml(value: string): string {
    return value
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  private buildInstitucionPopup(inst: Institucion): string {
    const direccion = this.escapeHtml(inst.direccion || 'Sin dirección registrada');
    const logo = inst.logo
      ? `<img src="${this.escapeHtml(inst.logo)}" alt="Logo de ${this.escapeHtml(inst.nombre)}" style="width:42px;height:42px;object-fit:contain;border-radius:8px;border:1px solid #d1d5db;background:#fff;padding:3px;flex-shrink:0;" />`
      : `<div style="width:42px;height:42px;border-radius:8px;border:1px solid #d1d5db;background:#f3f4f6;color:#4b5563;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">IFTS</div>`;

    return `
      <div style="display:flex;align-items:center;gap:10px;max-width:260px;">
        ${logo}
        <div style="font-size:13px;line-height:1.35;color:#1f2937;">${direccion}</div>
      </div>
    `;
  }

  private resetHighlightedMarker(): void {
    if (!this.highlightedMarker) {
      return;
    }

    this.resetMarkerStyle(this.highlightedMarker);
    this.highlightedMarker.closePopup();
    this.highlightedMarker = null;
  }

  private resetMarkerStyle(marker: L.CircleMarker): void {
    marker.setStyle({
      radius: 10,
      color: '#006633',
      fillColor: '#66bb6a',
      fillOpacity: 0.9,
      weight: 2
    });
  }
}
