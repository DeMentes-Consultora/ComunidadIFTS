# 📚 GUÍA COMPLETA: LEAFLET MARKERCLUSTER EN ANGULAR

## ¿QUÉ ES MARKER CLUSTERING?

**Clustering** es agrupar marcadores cercanos en un único punto. Por ejemplo:
- 50 instituciones en Buenos Aires → 1 cluster que dice "50"
- Cuando haces zoom → Se divide en grupos más pequeños
- Al llegar al zoom máximo → Se muestran marcadores individuales

**VENTAJAS:**
- ✅ Mejor rendimiento (menos elementos en pantalla)
- ✅ Visualización más clara
- ✅ Agrupa automáticamente por proximidad
- ✅ Los clusters son interactivos (clickeables)

---

## 📦 INSTALACIÓN

```bash
npm install leaflet.markercluster
npm install --save-dev @types/leaflet.markercluster
```

### CSS Global (src/styles.css)
```css
@import "leaflet.markercluster/dist/MarkerCluster.css";
@import "leaflet.markercluster/dist/MarkerCluster.Default.css";
```

---

## 🔧 CREAR SERVICIO (marker-cluster.service.ts)

El servicio abstrae la lógica de clustering para reutilizarla:

```typescript
import { Injectable } from '@angular/core';
import * as L from 'leaflet';
import 'leaflet.markercluster';

@Injectable({ providedIn: 'root' })
export class MarkerClusterService {
  private clusterGroup: L.MarkerClusterGroup | null = null;
  private map: L.Map | null = null;

  // 1️⃣ INICIALIZAR CLUSTER
  initClusterGroup(map: L.Map, options?: L.MarkerClusterGroupOptions) {
    this.map = map;
    
    // Opciones personalizables
    const defaultOptions: L.MarkerClusterGroupOptions = {
      maxClusterRadius: 80,        // Radius de agrupamiento en píxeles
      disableClusteringAtZoom: 18, // Mostrar individual en zoom 18+
      spiderfyOnMaxZoom: true,     // Modo "araña" si hay muchos
      zoomToBoundsOnClick: true    // Hacer zoom al click
    };

    this.clusterGroup = L.markerClusterGroup({
      ...defaultOptions,
      ...options // Permitir sobrescribir
    });

    this.map.addLayer(this.clusterGroup);
    return this.clusterGroup;
  }

  // 2️⃣ AGREGAR MARCADORES
  addMarker(marker: L.Marker) {
    this.clusterGroup?.addLayer(marker);
  }

  addMarkers(markers: L.Marker[]) {
    markers.forEach(m => this.clusterGroup?.addLayer(m));
  }

  // 3️⃣ LIMPIAR CLUSTERS
  clearAllMarkers() {
    this.clusterGroup?.clearLayers();
  }

  destroy() {
    this.map?.removeLayer(this.clusterGroup!);
    this.clusterGroup = null;
  }
}
```

---

## 🎯 USAR EN COMPONENTE (mapa.ts)

### 1. Inyectar servicio
```typescript
constructor(
  private markerClusterService: MarkerClusterService,
  // resto de inyecciones...
) {}
```

### 2. Inicializar en initMap()
```typescript
private initMap(): void {
  this.map = L.map('mapa-ifts').setView([-34.6037, -58.3816], 13);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
  }).addTo(this.map);

  // ✨ AQUÍ INICIOR EL CLUSTER
  this.markerClusterService.initClusterGroup(this.map, {
    maxClusterRadius: 80,
    disableClusteringAtZoom: 18
  });
}
```

### 3. Agregar marcadores al cluster (NO al mapa)
```typescript
private renderInstituciones(): void {
  this.instituciones.forEach((inst) => {
    // Crear marcador normal
    const marker = L.circleMarker([inst.latitud, inst.longitud], {
      radius: 10,
      color: '#006633'
    });

    marker.bindPopup(this.getPopupContent(inst));

    // ✨ AGREGAR AL CLUSTER EN LUGAR DE AL MAPA
    this.markerClusterService.addMarker(marker);
  });
}
```

### 4. Limpiar en ngOnDestroy()
```typescript
ngOnDestroy(): void {
  this.markerClusterService.destroy();
  this.map?.remove();
}
```

---

## 🎨 PERSONALIZAR CLUSTERS

### Colores por cantidad:
```typescript
iconCreateFunction: (cluster) => {
  const count = cluster.getChildCount();
  let bgColor = '#66bb6a'; // Verde: 0-10
  
  if (count > 10 && count < 50)
    bgColor = '#ffb300'; // Naranja: 10-50
  else if (count >= 50)
    bgColor = '#d32f2f'; // Rojo: 50+

  return new L.DivIcon({
    html: `<div style="background: ${bgColor}">${count}</div>`,
    iconSize: [40, 40]
  });
}
```

---

## ⚙️ OPCIONES PRINCIPALES

| Opción | Descrición | Valor |
|--------|-----------|-------|
| `maxClusterRadius` | Radio de agrupamiento (px) | `80` |
| `disableClusteringAtZoom` | Cluster desactivo en zoom X | `18` |
| `zoomToBoundsOnClick` | Hacer zoom al click en cluster | `true` |
| `spiderfyOnMaxZoom` | Modo araña si hay muchos | `true` |
| `iconCreateFunction` | Función para personalizar icono | `fn` |

---

## 📊 EJEMPLO COMPLETO

```typescript
// home.ts
import { Component, OnInit } from '@angular/core';
import { MapaComponent } from '@shared/components/mapa/mapa';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [MapaComponent],
  template: '<app-mapa></app-mapa>'
})
export class HomeComponent implements OnInit {
  ngOnInit() {
    console.log('Mapa con clustering listo 🗺️');
  }
}
```

---

## 🚀 USAR EN OTROS PROYECTOS

Solo copia estos 2 archivos:

1. **marker-cluster.service.ts** → `src/app/shared/services/`
2. **Los imports en styles.css**

```css
@import "leaflet.markercluster/dist/MarkerCluster.css";
@import "leaflet.markercluster/dist/MarkerCluster.Default.css";
```

Luego inyecta el servicio en cualquier componente con mapa:

```typescript
constructor(private markerClusterService: MarkerClusterService) {}
```

---

## 🐛 TROUBLESHOOTING

### ❌ "L.markerClusterGroup is not a function"
**Solución:** Asegúrate de importar en styles.css ambos archivos CSS

### ❌ Los marcadores no aparecen clustered
**Solución:** Verifica que `addLayer()` sea llamado al cluster, no al mapa

### ❌ Los clusters no se dividen al zoom
**Solución:** Revisa que `disableClusteringAtZoom` sea más alto que tu zoom inicial

---

## 📝 RESUMEN

| Paso | Qué hacer | Archivo |
|------|-----------|---------|
| 1 | Instalar librería | Terminal |
| 2 | Importar CSS | styles.css |
| 3 | Crear servicio | marker-cluster.service.ts |
| 4 | Inyectar en componente | mapa.ts constructor |
| 5 | Inicializar cluster | mapa.ts initMap() |
| 6 | Agregar marcadores al cluster | mapa.ts renderInstituciones() |
| 7 | Limpiar en destrucción | mapa.ts ngOnDestroy() |

---

## 🎯 RESULTADO

Con esto conseguiste:
✅ Clustering automático de marcadores
✅ Mejor rendimiento
✅ Interfaz más limpia
✅ Código reutilizable en otros proyectos
