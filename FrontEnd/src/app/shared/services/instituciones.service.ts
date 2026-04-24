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
  private apiActualizarUrl = `${environment.apiUrl}/actualizar-institucion.php`;
  private apiEliminarUrl = `${environment.apiUrl}/eliminar-institucion.php`;
  private apiLikeUrl = `${environment.apiUrl}/like-institucion.php`;

  constructor(private http: HttpClient) {}

  /**
   * Obtener todas las instituciones
   */
  obtenerTodas(): Observable<Institucion[]> {
    return this.http.get(this.apiUrl, { responseType: 'text' })
      .pipe(
        map(rawResponse => {
          const response = this.parseApiResponse<Institucion[]>(rawResponse);
          if (response.success && response.data) {
            return response.data;
          }
          throw new Error(response.message || 'Error al obtener instituciones');
        })
      );
  }

  /**
   * Obtener todas las carreras disponibles (solo nombres, para compatibilidad)
   */
  obtenerCarreras(): Observable<string[]> {
    return this.obtenerCarrerasConId().pipe(
      map(carreras => carreras.map(c => c.nombre))
    );
  }

  /**
   * Obtener todas las carreras disponibles con id y nombre
   */
  obtenerCarrerasConId(): Observable<Array<{ id: number; nombre: string }>> {
    return this.http.get(this.apiCarrerasUrl, { responseType: 'text' })
      .pipe(
        map(rawResponse => {
          const response = this.parseApiResponse<any[]>(rawResponse);
          if (response.success && response.data) {
            // Mapear array de objetos con id y nombre
            return response.data.map((carrera: any) => ({
              id: carrera.id || carrera.id_carrera,
              nombre: carrera.nombre || carrera.nombre_carrera
            }));
          }
          throw new Error(response.message || 'Error al obtener carreras');
        })
      );
  }

  private parseApiResponse<T>(rawResponse: unknown): ApiResponse<T> {
    if (typeof rawResponse !== 'string') {
      return rawResponse as ApiResponse<T>;
    }

    const text = rawResponse.replace(/^\uFEFF/, '').trim();
    if (!text) {
      throw new Error('El servidor devolvio una respuesta vacia');
    }

    if (this.looksLikeInfinityFreeChallenge(text)) {
      throw new Error('El hosting devolvio una pagina de verificacion (InfinityFree) en lugar de JSON');
    }

    const parsedDirect = this.tryParseJson<ApiResponse<T>>(text);
    if (parsedDirect !== null) {
      return parsedDirect;
    }

    const preJson = this.extractJsonFromPreTag(text);
    if (preJson) {
      const parsedFromPre = this.tryParseJson<ApiResponse<T>>(preJson);
      if (parsedFromPre !== null) {
        return parsedFromPre;
      }
    }

    const start = text.indexOf('{');
    const end = text.lastIndexOf('}');
    if (start !== -1 && end > start) {
      const candidate = text.slice(start, end + 1);
      const parsedCandidate = this.tryParseJson<ApiResponse<T>>(candidate);
      if (parsedCandidate !== null) {
        return parsedCandidate;
      }
    }

    throw new Error('El servidor devolvio una respuesta invalida');
  }

  private extractJsonFromPreTag(text: string): string | null {
    const match = text.match(/<pre[^>]*>([\s\S]*?)<\/pre>/i);
    if (!match || !match[1]) {
      return null;
    }

    return match[1]
      .replace(/&quot;/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .trim();
  }

  private looksLikeInfinityFreeChallenge(text: string): boolean {
    const lower = text.toLowerCase();
    return lower.includes('/aes.js') || lower.includes('toNumbers(') || lower.includes('openresty');
  }

  private tryParseJson<T>(text: string): T | null {
    try {
      return JSON.parse(text) as T;
    } catch {
      return null;
    }
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
    return this.http.post(this.apiGuardarUrl, institucion, { withCredentials: true });
  }

  /**
   * Guardar institución con todos los datos (incluyendo carreras)
   */
  guardarInstitucion(datos: any): Observable<any> {
    return this.http.post(this.apiGuardarUrl, datos, { withCredentials: true });
  }

  /**
   * Actualizar institución existente
   */
  actualizarInstitucion(datos: any): Observable<any> {
    if (datos instanceof FormData) {
      return this.http.post(this.apiActualizarUrl, datos, { withCredentials: true });
    }
    return this.http.put(this.apiActualizarUrl, datos, { withCredentials: true });
  }

  /**
   * Eliminar institución
   */
  eliminarInstitucion(id_institucion: number): Observable<any> {
    return this.http.post(this.apiEliminarUrl, { id_institucion }, { withCredentials: true });
  }
}
