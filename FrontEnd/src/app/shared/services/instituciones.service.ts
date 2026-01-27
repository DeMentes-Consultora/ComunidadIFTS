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
   * Dar like a una institución
   */
  darLike(id: number): Observable<any> {
    return this.http.post(`${environment.apiUrl}/like-institucion.php`, { id });
  }

  /**
   * Guardar nueva institución
   */
  guardar(institucion: Partial<Institucion>): Observable<any> {
    return this.http.post(`${environment.apiUrl}/guardar-institucion.php`, institucion);
  }
}
