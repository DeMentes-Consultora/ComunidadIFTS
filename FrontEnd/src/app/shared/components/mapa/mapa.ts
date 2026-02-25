import { Component, AfterViewInit, OnDestroy, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as L from 'leaflet';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { InstitucionesService } from '../../services/instituciones.service';
import { AuthService } from '../../services/auth.service';
import { MarkerClusterService } from '../../services/marker-cluster.service';
import { Institucion } from '../../models/institucion.model';
import { BuscadorDireccionComponent } from '../buscador-direccion/buscador-direccion';
import { FormularioInstitucionComponent } from '../formulario-institucion/formulario-institucion';

@Component({
  selector: 'app-mapa',
  standalone: true,
  imports: [
    CommonModule, 
    BuscadorDireccionComponent, 
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    MatChipsModule,
    FormularioInstitucionComponent
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
  private isLoading = true;
  private useClustering = false;
  private popupCloseTimers = new Map<number, ReturnType<typeof setTimeout>>();
  private markersByInstitucionId = new Map<number, L.CircleMarker>();
  private renderedMarkers: L.CircleMarker[] = [];
  private carrerasFiltradasIds: number[] = [];
  
  // Propiedades para el formulario y permisos
  mostrarFormulario = false;
  modoEdicion = false;
  canEdit = false;

  constructor(
    private institucionesService: InstitucionesService,
    private authService: AuthService,
    private markerClusterService: MarkerClusterService,
    private dialog: MatDialog,
    private cdr: ChangeDetectorRef
  ) {
    this.verificarPermisos();
  }

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

  /**
   * Verificar si el usuario actual tiene permisos para editar IFTS
   * Sistema de permisos por ID de rol:
   * - ID 1: AdministradorComunidad (puede editar)
   * - ID 7: AdministradorIFTS (puede editar)
   * - Cualquier otro ID: Solo lectura
   */
  private verificarPermisos(): void {
    const usuarioActual = this.authService.getCurrentUser();
    if (usuarioActual) {
      // Solo los roles 1 y 7 pueden editar IFTS
      this.canEdit = [1, 7].includes(usuarioActual.id_rol);
    }
  }

  /**
   * Cambiar modo del formulario (nuevo, editar o cerrar)
   */
  toggleFormMode(modo: 'nuevo' | 'editar' | null): void {
    if (modo === null) {
      this.mostrarFormulario = false;
      this.modoEdicion = false;
    } else {
      this.mostrarFormulario = true;
      this.modoEdicion = modo === 'editar';
    }
    this.cdr.markForCheck();
  }

  /**
   * Obtener coordenadas de una institución
   */
  getCoordenadasInstitucion(inst: Institucion): L.LatLng {
    return L.latLng(inst.latitud, inst.longitud);
  }

  /**
   * Manejar institución guardada
   */
  onInstitucionGuardada(evento: any): void {
    // Cerrar el formulario
    this.mostrarFormulario = false;
    this.modoEdicion = false;
    // Recargar las instituciones
    this.cargarInstituciones();
    this.cdr.markForCheck();
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
    // Seleccionar la institución en el panel lateral
    const institucion = this.instituciones.find(i => i.id === idInstitucion);
    if (institucion) {
      this.institucionSeleccionada = institucion;
      this.mostrarFormulario = false;
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

  /**
   * Resaltar un marcador visualmente
   */
  private highlightMarker(marker: L.CircleMarker): void {
    marker.setStyle({
      radius: 14,
      fillColor: '#22c55e',
      weight: 3
    });

    // Restaurar estilo después de 1 segundo
    setTimeout(() => {
      marker.setStyle({
        radius: 10,
        fillColor: '#66bb6a',
        weight: 2
      });
    }, 1000);
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

  /**
   * Limpiar marcadores de manera segura
   */
  private limpiarMarcadores(): void {
    // Limpiar el mapa del elemento _leaflet_id
    this.markersByInstitucionId.clear();

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

  private escapeHtml(value: string): string {
    return value
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
}
