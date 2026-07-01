import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import {
  ForoCategoria,
  ForoCategoriaResponse,
  ForoListadoResponse,
  ForoTemaResponse,
  ForoRespuestasResponse,
  ForoBusquedaResponse,
  ForoTema,
  ForoRespuesta
} from '../models/foro.model';

@Injectable({ providedIn: 'root' })
export class ForoService {
  private readonly api = environment.apiUrl;

  constructor(private readonly http: HttpClient) {}

  // ------------------------------------------------------------------
  // CATEGORÍAS
  // ------------------------------------------------------------------

  getCategorias(admin = false): Observable<ForoCategoria[]> {
    let params = new HttpParams();
    if (admin) params = params.set('admin', '1');
    return this.http
      .get<ForoCategoriaResponse>(`${this.api}/foro-categorias.php`, { params, withCredentials: true })
      .pipe(map((r) => r.categorias ?? []));
  }

  crearCategoria(data: Partial<ForoCategoria>): Observable<{ success: boolean; id_categoria: number }> {
    return this.http.post<{ success: boolean; id_categoria: number }>(
      `${this.api}/foro-categorias-gestion.php`, data, { withCredentials: true }
    );
  }

  actualizarCategoria(data: Partial<ForoCategoria>): Observable<{ success: boolean }> {
    return this.http.put<{ success: boolean }>(
      `${this.api}/foro-categorias-gestion.php`, data, { withCredentials: true }
    );
  }

  eliminarCategoria(id_categoria: number): Observable<{ success: boolean }> {
    return this.http.delete<{ success: boolean }>(
      `${this.api}/foro-categorias-gestion.php`, {
        withCredentials: true,
        body: { id_categoria }
      }
    );
  }

  // ------------------------------------------------------------------
  // TEMAS
  // ------------------------------------------------------------------

  getTemas(page = 1, limit = 15, categoriaId?: number, busqueda?: string): Observable<ForoListadoResponse> {
    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());
    if (categoriaId) params = params.set('categoria', categoriaId.toString());
    if (busqueda) params = params.set('q', busqueda);
    return this.http.get<ForoListadoResponse>(`${this.api}/foro-temas.php`, { params, withCredentials: true });
  }

  getTema(id: number): Observable<ForoTemaResponse> {
    return this.http.get<ForoTemaResponse>(`${this.api}/foro-tema.php`, {
      params: new HttpParams().set('id', id.toString()),
      withCredentials: true
    });
  }

  crearTema(data: { id_categoria: number; titulo: string; contenido: string }): Observable<{ success: boolean; id_tema: number }> {
    return this.http.post<{ success: boolean; id_tema: number }>(
      `${this.api}/foro-tema-crear.php`, data, { withCredentials: true }
    );
  }

  actualizarTema(data: Partial<ForoTema> & { id_tema: number }): Observable<{ success: boolean }> {
    return this.http.put<{ success: boolean }>(`${this.api}/foro-tema.php`, data, { withCredentials: true });
  }

  accionTema(id_tema: number, accion: string, payload?: Record<string, unknown>): Observable<{ success: boolean; message: string }> {
    return this.http.put<{ success: boolean; message: string }>(
      `${this.api}/foro-tema.php`, { id_tema, accion, ...payload }, { withCredentials: true }
    );
  }

  eliminarTema(id_tema: number): Observable<{ success: boolean }> {
    return this.http.delete<{ success: boolean }>(`${this.api}/foro-tema.php`, {
      withCredentials: true,
      body: { id_tema }
    });
  }

  // ------------------------------------------------------------------
  // RESPUESTAS
  // ------------------------------------------------------------------

  getRespuestas(id_tema: number, page = 1, limit = 20): Observable<ForoRespuestasResponse> {
    let params = new HttpParams()
      .set('id_tema', id_tema.toString())
      .set('page', page.toString())
      .set('limit', limit.toString());
    return this.http.get<ForoRespuestasResponse>(`${this.api}/foro-respuestas.php`, { params, withCredentials: true });
  }

  crearRespuesta(data: { id_tema: number; contenido: string; citando_id?: number | null }): Observable<{ success: boolean; id_respuesta: number }> {
    return this.http.post<{ success: boolean; id_respuesta: number }>(
      `${this.api}/foro-respuestas.php`, data, { withCredentials: true }
    );
  }

  actualizarRespuesta(data: { id_respuesta: number; contenido: string }): Observable<{ success: boolean }> {
    return this.http.put<{ success: boolean }>(`${this.api}/foro-respuesta.php`, data, { withCredentials: true });
  }

  eliminarRespuesta(id_respuesta: number): Observable<{ success: boolean }> {
    return this.http.delete<{ success: boolean }>(`${this.api}/foro-respuesta.php`, {
      withCredentials: true,
      body: { id_respuesta }
    });
  }

  // ------------------------------------------------------------------
  // BÚSQUEDA
  // ------------------------------------------------------------------

  buscar(termino: string, tipo: 'temas' | 'respuestas' = 'temas', categoriaId?: number, page = 1): Observable<ForoBusquedaResponse> {
    let params = new HttpParams()
      .set('q', termino)
      .set('tipo', tipo)
      .set('page', page.toString());
    if (categoriaId) params = params.set('categoria', categoriaId.toString());
    return this.http.get<ForoBusquedaResponse>(`${this.api}/foro-buscar.php`, { params, withCredentials: true });
  }
}
