import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface MateriaGestion {
  id_materia: number;
  nombre_materia: string;
}

export interface CarreraGestion {
  id_carrera: number;
  nombre_carrera: string;
  materias: MateriaGestion[];
}

export interface GestionCarrerasData {
  materias: MateriaGestion[];
  carreras: CarreraGestion[];
}

@Injectable({
  providedIn: 'root',
})
export class GestionCarrerasService {
  private http = inject(HttpClient);
  private endpoint = `${environment.apiUrl}/gestion-carreras.php`;

  obtenerEstado(): Observable<{ success: boolean; data: GestionCarrerasData; message?: string }> {
    return this.http.get<{ success: boolean; data: GestionCarrerasData; message?: string }>(this.endpoint, {
      withCredentials: true,
    });
  }

  asociarMateria(id_carrera: number, id_materia: number): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'asociar', id_carrera, id_materia },
      { withCredentials: true }
    );
  }

  desasociarMateria(id_carrera: number, id_materia: number): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'desasociar', id_carrera, id_materia },
      { withCredentials: true }
    );
  }

  crearCarrera(nombre_carrera: string): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'crear_carrera', nombre_carrera },
      { withCredentials: true }
    );
  }

  editarCarrera(id_carrera: number, nombre_carrera: string): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'editar_carrera', id_carrera, nombre_carrera },
      { withCredentials: true }
    );
  }

  eliminarCarrera(id_carrera: number): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'eliminar_carrera', id_carrera },
      { withCredentials: true }
    );
  }

  crearMateria(nombre_materia: string): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'crear_materia', nombre_materia },
      { withCredentials: true }
    );
  }

  editarMateria(id_materia: number, nombre_materia: string): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'editar_materia', id_materia, nombre_materia },
      { withCredentials: true }
    );
  }

  eliminarMateria(id_materia: number): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      this.endpoint,
      { accion: 'eliminar_materia', id_materia },
      { withCredentials: true }
    );
  }
}
