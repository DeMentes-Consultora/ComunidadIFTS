import { Injectable } from '@angular/core';
import * as L from 'leaflet';

// leaflet.markercluster se carga desde angular.json (scripts)
// y extiende el objeto L globalmente con markerClusterGroup

// Declarar tipos para leaflet.markercluster que no exporta TypeScript
declare global {
  namespace L {
    function markerClusterGroup(options?: any): any;
    interface MarkerClusterGroupOptions {
      maxClusterRadius?: number;
      zoomToBoundsOnClick?: boolean;
      spiderfyOnMaxZoom?: boolean;
      disableClusteringAtZoom?: number;
      iconCreateFunction?: (cluster: any) => L.Icon;
      [key: string]: any;
    }
  }
}

/**
 * Servicio para manejar clustering de marcadores en Leaflet
 * 
 * ¿QUÉ ES UN CLUSTER?
 * Un cluster agrupa marcadores cercanos en un único punto con un número.
 * Cuando haces zoom, el cluster se divide en grupos más pequeños.
 * 
 * VENTAJAS:
 * - Mejora rendimiento (menos elementos en pantalla)
 * - Visualización más clara con muchos marcadores
 * - Agrupa automáticamente por proximidad
 */
@Injectable({
  providedIn: 'root'
})
export class MarkerClusterService {
  private clusterGroup: any = null;
  private map: L.Map | null = null;
  private pluginLoadPromise: Promise<boolean> | null = null;

  private async ensureClusterPluginLoaded(): Promise<boolean> {
    if (this.isClusterAvailable()) {
      return true;
    }

    if (this.pluginLoadPromise) {
      return this.pluginLoadPromise;
    }

    this.pluginLoadPromise = (async () => {
      try {
        (window as any).L = L;
        await import('leaflet.markercluster');
        return this.isClusterAvailable();
      } catch (error) {
        console.error('No se pudo cargar leaflet.markercluster dinámicamente', error);
        return false;
      }
    })();

    return this.pluginLoadPromise;
  }

  isClusterAvailable(): boolean {
    return typeof (L as any).markerClusterGroup === 'function';
  }

  /**
   * Inicializar el cluster group
   * @param map Instancia del mapa Leaflet
   * @param options Opciones de configuración del cluster
   */
  async initClusterGroup(map: L.Map, options?: any): Promise<any> {
    this.map = map;

    const loaded = await this.ensureClusterPluginLoaded();

    // Verificar que L.markerClusterGroup está disponible
    if (!loaded || !this.isClusterAvailable()) {
      console.error('ERROR: L.markerClusterGroup no está disponible');
      console.error('No se pudo inicializar el plugin leaflet.markercluster en runtime');
      return null;
    }

    // Opciones por defecto del cluster
    const defaultOptions: L.MarkerClusterGroupOptions = {
      maxClusterRadius: 80, // Radio máximo de agrupamiento (píxeles)
      zoomToBoundsOnClick: true, // Hacer zoom al hacer click en un cluster
      spiderfyOnMaxZoom: true, // Mostrar marcadores en araña cuando no caben más
      disableClusteringAtZoom: 18, // Desactivar clustering en zoom 18+
      iconCreateFunction: (cluster) => {
        // Colores personalizados según cantidad de marcadores
        const count = cluster.getChildCount();
        let bgColor = '#66bb6a'; // Verde claro (0-10 marcadores)
        let size = 40;

        if (count > 10 && count < 50) {
          bgColor = '#ffb300'; // Naranja (10-50 marcadores)
          size = 50;
        } else if (count >= 50) {
          bgColor = '#d32f2f'; // Rojo (50+ marcadores)
          size = 60;
        }

        return new L.DivIcon({
          html: `
            <div style="
              background: ${bgColor};
              border: 2px solid white;
              border-radius: 50%;
              width: ${size}px;
              height: ${size}px;
              display: flex;
              align-items: center;
              justify-content: center;
              font-weight: bold;
              color: white;
              font-size: 14px;
              box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            ">
              ${count}
            </div>
          `,
          className: 'marker-cluster-custom',
          iconSize: new L.Point(size, size),
          iconAnchor: new L.Point(size / 2, size / 2)
        });
      },
      ...options // Permitir sobrescribir opciones por defecto
    };

    // Crear el cluster group
    this.clusterGroup = (L as any).markerClusterGroup(defaultOptions);
    
    // Agregar a la mapa
    this.map.addLayer(this.clusterGroup);

    return this.clusterGroup;
  }

  /**
   * Agregar un marcador al cluster (acepta Marker, CircleMarker, etc)
   * @param marker Marcador Leaflet a agregar (cualquier tipo de Layer)
   */
  addMarker(marker: L.Layer): void {
    if (!this.clusterGroup) {
      return;
    }
    this.clusterGroup.addLayer(marker);
  }

  /**
   * Agregar múltiples marcadores al cluster
   * @param markers Array de marcadores
   */
  addMarkers(markers: L.Layer[]): void {
    if (!this.clusterGroup) {
      return;
    }
    markers.forEach(marker => this.clusterGroup!.addLayer(marker));
  }

  /**
   * Remover un marcador del cluster
   * @param marker Marcador a remover
   */
  removeMarker(marker: L.Layer): void {
    if (!this.clusterGroup) return;
    this.clusterGroup.removeLayer(marker);
  }

  /**
   * Limpiar todos los marcadores del cluster
   */
  clearAllMarkers(): void {
    if (!this.clusterGroup) return;
    this.clusterGroup.clearLayers();
  }

  /**
   * Obtener la cantidad total de marcadores en el cluster
   */
  getMarkerCount(): number {
    if (!this.clusterGroup) return 0;
    return this.clusterGroup.getLayers().length;
  }

  /**
   * Obtener el cluster group actual
   */
  getClusterGroup(): any {
    return this.clusterGroup;
  }

  /**
   * Ajustar vista al zoom de todos los marcadores
   */
  zoomToAllMarkers(): void {
    if (!this.clusterGroup || !this.map) return;
    const bounds = this.clusterGroup.getBounds();
    if (bounds.isValid()) {
      this.map.fitBounds(bounds, { padding: [50, 50] });
    }
  }

  /**
   * Destruir el cluster group (limpieza)
   */
  destroy(): void {
    if (!this.clusterGroup || !this.map) return;
    this.map.removeLayer(this.clusterGroup);
    this.clusterGroup = null;
    this.map = null;
  }
}
