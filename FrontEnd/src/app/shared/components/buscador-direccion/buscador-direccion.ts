import { Component, Input, Output, EventEmitter, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as L from 'leaflet';
import 'leaflet-control-geocoder';
import { Institucion } from '../../models/institucion.model';

@Component({
  selector: 'app-buscador-direccion',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './buscador-direccion.html',
  styleUrls: ['./buscador-direccion.css']
})
export class BuscadorDireccionComponent implements OnInit, OnDestroy {
  @Input() map: L.Map | null = null;
  @Input() instituciones: Institucion[] = [];
  @Output() direccionEncontrada = new EventEmitter<{ coordenadas: L.LatLng; direccion: string }>();
  @Output() institucionSeleccionada = new EventEmitter<number>();

  private geocoderControl: any;

  ngOnInit(): void {
    if (this.map) {
      this.inicializarGeocoder();
    }
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
}
