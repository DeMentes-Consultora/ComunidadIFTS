import { Component, Input, Output, EventEmitter, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import * as L from 'leaflet';
import 'leaflet-control-geocoder';

@Component({
  selector: 'app-buscador-direccion',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './buscador-direccion.html',
  styleUrls: ['./buscador-direccion.css']
})
export class BuscadorDireccionComponent implements OnInit, OnDestroy {
  @Input() map: L.Map | null = null;
  @Output() direccionEncontrada = new EventEmitter<{ coordenadas: L.LatLng; direccion: string }>();

  private geocoder: any;

  ngOnInit(): void {
    if (this.map) {
      this.inicializarGeocoder();
    }
  }

  ngOnDestroy(): void {
    // Limpiar resources si es necesario
  }

  private inicializarGeocoder(): void {
    if (!this.map) return;

    // Crear el control de geocoder de Leaflet
    const control = (L.Control as any).geocoder({
      defaultMarkGeocode: false,
      collapsed: false,
      placeholder: "Buscar dirección en Argentina...",
      geocoder: (L.Control as any).Geocoder.arcgis({
        geocodingQueryParams: {
          sourceCountry: 'ARG'
        }
      })
    });

    control.on('markgeocode', (e: any) => {
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

      // Emitir el evento con la ubicación encontrada
      setTimeout(() => {
        this.direccionEncontrada.emit({
          coordenadas: e.geocode.center,
          direccion: e.geocode.name || e.geocode.address || 'Dirección desconocida'
        });
      }, 500);
    });

    control.addTo(this.map);
  }
}
