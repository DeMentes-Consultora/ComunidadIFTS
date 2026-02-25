import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatSelectModule } from '@angular/material/select';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatChipsModule } from '@angular/material/chips';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import * as L from 'leaflet';
import 'leaflet-control-geocoder';
import { Institucion } from '../../models/institucion.model';
import { InstitucionesService } from '../../services/instituciones.service';

interface Carrera {
  id: number;
  nombre: string;
}

@Component({
  selector: 'app-buscador-direccion',
  standalone: true,
  imports: [
    CommonModule, 
    FormsModule, 
    MatSelectModule, 
    MatFormFieldModule, 
    MatChipsModule, 
    MatIconModule,
    MatInputModule
  ],
  templateUrl: './buscador-direccion.html',
  styleUrls: ['./buscador-direccion.css']
})
export class BuscadorDireccionComponent implements OnInit, OnDestroy {
  @Input() map: L.Map | null = null;
  @Input() instituciones: Institucion[] = [];
  @Output() direccionEncontrada = new EventEmitter<{ coordenadas: L.LatLng; direccion: string }>();
  @Output() institucionSeleccionada = new EventEmitter<number>();
  @Output() carrerasFiltradas = new EventEmitter<number[]>();
  
  @ViewChild('searchInput') searchInput?: ElementRef<HTMLInputElement>;

  modoBusqueda: 'direccion' | 'carrera' = 'direccion';
  carrerasDisponibles: Carrera[] = [];
  carrerasFiltradasList: Carrera[] = []; // Lista filtrada para búsqueda
  carrerasSeleccionadas: number[] = [];
  institucionesFiltradas = 0;
  searchCarreraText = ''; // Texto de búsqueda de carreras

  private geocoderControl: any;

  constructor(private institucionesService: InstitucionesService) {}

  ngOnInit(): void {
    if (this.map && this.modoBusqueda === 'direccion') {
      this.inicializarGeocoder();
    }
    this.cargarCarreras();
  }

  ngOnDestroy(): void {
    if (this.map && this.geocoderControl) {
      this.map.removeControl(this.geocoderControl);
    }
  }

  private inicializarGeocoder(): void {
    if (!this.map) return;

    const arcgisGeocoder = (L.Control as any).Geocoder.arcgis({
      geocodingQueryParams: {
        sourceCountry: 'ARG'
      }
    });

    const customGeocoder = {
      geocode: async (query: string, context: any) => {
        const locales = this.buscarInstitucionesLocales(query);
        let remotos: any = [];

        try {
          remotos = await arcgisGeocoder.geocode(query, context);
        } catch {
          remotos = [];
        }

        return this.combinarResultados(locales, remotos);
      },
      suggest: async (query: string, context: any) => {
        const locales = this.buscarInstitucionesLocales(query);

        if (typeof arcgisGeocoder.suggest === 'function') {
          try {
            const remotos = await arcgisGeocoder.suggest(query, context);
            return this.combinarResultados(locales, remotos);
          } catch {
            return this.combinarResultados(locales, []);
          }
        }

        return this.combinarResultados(locales, []);
      },
      reverse: async (location: any, scale: number, context: any) => {
        if (typeof arcgisGeocoder.reverse === 'function') {
          try {
            const remotos = await arcgisGeocoder.reverse(location, scale, context);
            return this.normalizarResultados(remotos);
          } catch {
            return [];
          }
        }

        return [];
      }
    };

    // Crear el control de geocoder de Leaflet
    this.geocoderControl = (L.Control as any).geocoder({
      defaultMarkGeocode: false,
      collapsed: false,
      placeholder: "Buscar IFTS o dirección en Argentina...",
      geocoder: customGeocoder
    });

    this.geocoderControl.on('markgeocode', (e: any) => {
      if (!this.map) return;

      // Cerrar cualquier popup abierto
      this.map.closePopup();

      // Centrar el mapa en la dirección encontrada
      const bbox = e.geocode.bbox;
      if (bbox) {
        this.map.fitBounds(bbox);
      } else {
        this.map.setView(e.geocode.center, 16);
      }

      // Solo emitir evento para direcciones externas, no para IFTS existentes
      const esInstitucion = e?.geocode?.properties?.source === 'institucion';
      if (esInstitucion) {
        const idInstitucion = Number(e?.geocode?.properties?.id);
        if (!Number.isNaN(idInstitucion) && idInstitucion > 0) {
          this.institucionSeleccionada.emit(idInstitucion);
        }
      } else {
        setTimeout(() => {
          this.direccionEncontrada.emit({
            coordenadas: e.geocode.center,
            direccion: e.geocode.name || e.geocode.address || 'Dirección desconocida'
          });
        }, 500);
      }
    });

    this.geocoderControl.addTo(this.map);
  }

  private combinarResultados(locales: any[], remotos: any): any[] {
    return [...locales, ...this.normalizarResultados(remotos)];
  }

  private normalizarResultados(resultados: any): any[] {
    if (Array.isArray(resultados)) {
      return resultados;
    }

    if (resultados && Array.isArray(resultados.results)) {
      return resultados.results;
    }

    return [];
  }

  private buscarInstitucionesLocales(query: string): any[] {
    const termino = (query || '').trim().toLowerCase();
    if (termino.length < 2) {
      return [];
    }

    return this.instituciones
      .filter((inst) => {
        const nombre = (inst.nombre || '').toLowerCase();
        const direccion = (inst.direccion || '').toLowerCase();
        return nombre.includes(termino) || direccion.includes(termino);
      })
      .slice(0, 10)
      .map((inst) => {
        const lat = Number(inst.latitud);
        const lng = Number(inst.longitud);
        if (Number.isNaN(lat) || Number.isNaN(lng)) {
          return null;
        }

        const center = L.latLng(lat, lng);
        const delta = 0.0008;
        const bbox = L.latLngBounds(
          [lat - delta, lng - delta],
          [lat + delta, lng + delta]
        );

        return {
          name: `${inst.nombre}${inst.direccion ? ' - ' + inst.direccion : ''}`,
          center,
          bbox,
          properties: {
            source: 'institucion',
            id: inst.id
          }
        };
      })
      .filter((resultado): resultado is { name: string; center: L.LatLng; bbox: L.LatLngBounds; properties: { source: string; id: number } } => resultado !== null);
  }

  /**
   * Cargar carreras disponibles
   */
  private cargarCarreras(): void {
    this.institucionesService.obtenerCarrerasConId().subscribe({
      next: (carreras) => {
        this.carrerasDisponibles = carreras;
        this.carrerasFiltradasList = carreras; // Inicialmente todas visibles
      },
      error: (err) => {
        console.error('Error cargando carreras:', err);
      }
    });
  }

  /**
   * Filtrar carreras según texto de búsqueda
   */
  filtrarCarreras(): void {
    const searchText = this.searchCarreraText.toLowerCase().trim();
    
    if (!searchText) {
      this.carrerasFiltradasList = this.carrerasDisponibles;
      return;
    }

    this.carrerasFiltradasList = this.carrerasDisponibles.filter(carrera =>
      carrera.nombre.toLowerCase().includes(searchText)
    );
  }

  /**
   * Obtener nombre de carrera por ID
   */
  getNombreCarrera(id: number): string {
    const carrera = this.carrerasDisponibles.find(c => c.id === id);
    return carrera ? carrera.nombre : '';
  }

  /**
   * Remover carrera seleccionada (desde chip)
   */
  removerCarrera(idCarrera: number): void {
    this.carrerasSeleccionadas = this.carrerasSeleccionadas.filter(id => id !== idCarrera);
    this.aplicarFiltroCarreras();
  }

  /**
   * Manejar cambio de modo de búsqueda
   */
  onModoChange(): void {
    if (this.modoBusqueda === 'direccion') {
      // Activar geocoder
      if (this.map && !this.geocoderControl) {
        this.inicializarGeocoder();
      }
      // Limpiar filtro de carreras
      this.limpiarFiltros();
    } else {
      // Desactivar geocoder
      if (this.map && this.geocoderControl) {
        this.map.removeControl(this.geocoderControl);
        this.geocoderControl = null;
      }
    }
  }

  /**
   * Manejar cambio de selección de carreras
   */
  onCarrerasChange(): void {
    this.aplicarFiltroCarreras();
  }

  /**
   * Aplicar filtro de carreras
   */
  private aplicarFiltroCarreras(): void {
    if (this.carrerasSeleccionadas.length === 0) {
      // Sin filtro, mostrar todas
      this.institucionesFiltradas = this.instituciones.length;
      this.carrerasFiltradas.emit([]);
    } else {
      // Contar instituciones que tienen al menos una carrera seleccionada
      const institucionesFiltradas = this.instituciones.filter(inst => {
        if (!inst.carreras || inst.carreras.length === 0) return false;
        
        return inst.carreras.some(carrera => 
          this.carrerasSeleccionadas.includes(carrera.id)
        );
      });
      
      this.institucionesFiltradas = institucionesFiltradas.length;
      this.carrerasFiltradas.emit(this.carrerasSeleccionadas);
    }
  }

  /**
   * Limpiar filtros de carrera
   */
  limpiarFiltros(): void {
    this.carrerasSeleccionadas = [];
    this.institucionesFiltradas = this.instituciones.length;
    this.carrerasFiltradas.emit([]);
  }

  /**
   * Manejar input de búsqueda (para direcciones)
   */
  onSearchInput(event: Event): void {
    // El geocoder de Leaflet maneja esto automáticamente
  }
}

