import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Institucion, ApiResponse } from '../models/institucion.model';
import { environment } from '../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class InstitucionesService {
  private apiUrl = `${environment.apiUrl}/instituciones.php`;
  private apiCarrerasUrl = `${environment.apiUrl}/carreras.php`;
  private apiGuardarUrl = `${environment.apiUrl}/guardar-institucion.php`;
  private apiLikeUrl = `${environment.apiUrl}/like-institucion.php`;

  constructor(private http: HttpClient) {}

  /**
   * Obtener todas las instituciones
   */
  obtenerTodas(): Observable<Institucion[]> {
    return this.http.get<ApiResponse<Institucion[]>>(this.apiUrl)
      .pipe(
        map(response => {
          if (response.success && response.data) {
            return response.data;
          }
          throw new Error(response.message || 'Error al obtener instituciones');
        })
      );
  }

  /**
   * Obtener todas las carreras disponibles
   */
  obtenerCarreras(): Observable<string[]> {
    return this.http.get<ApiResponse<any[]>>(this.apiCarrerasUrl)
      .pipe(
        map(response => {
          if (response.success && response.data) {
            // Mapear array de objetos a array de nombres
            return response.data.map((carrera: any) => carrera.nombre);
          }
          throw new Error(response.message || 'Error al obtener carreras');
        })
      );
  }

  /**
   * Dar like a una institución
   */
  darLike(id: number): Observable<any> {
    return this.http.post(this.apiLikeUrl, { id });
  }

  /**
   * Guardar nueva institución
   */
  guardar(institucion: Partial<Institucion>): Observable<any> {
    return this.http.post(this.apiGuardarUrl, institucion);
  }

  /**
   * Guardar institución con todos los datos (incluyendo carreras)
   */
  guardarInstitucion(datos: any): Observable<any> {
    return this.http.post(this.apiGuardarUrl, datos);
  }
}
