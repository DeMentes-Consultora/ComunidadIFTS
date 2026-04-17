import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

// ---- Interfaces ----

export interface OfertaLaboral {
  id_bolsaDeTrabajo: number;
  tituloOferta: string;
  textoOferta: string;
  fecha_creacion: string;
  fecha_creacion_formateada?: string;
  id_institucion: number;
  nombre_ifts: string;
  email_ifts: string;
  logo_ifts?: string | null;
  total_postulaciones?: number;
  // Solo en lista para alumnos
  ya_postulado?: boolean;
  // Solo en lista para admin (pendientes)
  nombre_creador?: string;
  apellido_creador?: string;
}

export interface CrearOfertaPayload {
  tituloOferta: string;
  textoOferta: string;
}

export interface GestionarOfertaPayload {
  id_bolsaDeTrabajo: number;
  accion: 'aprobar' | 'rechazar' | 'deshabilitar';
  motivo?: string;
}

export interface OfertasResponse {
  success: boolean;
  message?: string;
  data: OfertaLaboral[];
  total: number;
  seccion?: string;
}

export interface CrearOfertaResponse {
  success: boolean;
  message?: string;
  id_bolsaDeTrabajo?: number;
  mail_enviado?: boolean;
}

export interface GestionarOfertaResponse {
  success: boolean;
  message?: string;
  accion?: string;
}

export interface PostularseResponse {
  success: boolean;
  message?: string;
  id_postulacion?: number;
}

// ---- Service ----

@Injectable({ providedIn: 'root' })
export class BolsaTrabajoService {
  private http = inject(HttpClient);
  private base  = environment.apiUrl;

  /** (Rol IFTS-3) Crear oferta laboral */
  crearOferta(payload: CrearOfertaPayload): Observable<CrearOfertaResponse> {
    return this.http.post<CrearOfertaResponse>(
      `${this.base}/crear-oferta.php`,
      payload,
      { withCredentials: true }
    );
  }

  /** (Rol alumno) Ver ofertas publicadas */
  obtenerOfertasPublicadas(): Observable<OfertasResponse> {
    return this.http.get<OfertasResponse>(
      `${this.base}/ofertas-publicadas.php`,
      { withCredentials: true }
    );
  }

  /** (Rol admin-1) Ver ofertas según sección */
  obtenerOfertasAdmin(seccion: 'pendientes' | 'publicadas'): Observable<OfertasResponse> {
    return this.http.get<OfertasResponse>(
      `${this.base}/ofertas-pendientes.php?seccion=${seccion}`,
      { withCredentials: true }
    );
  }

  /** (Rol admin-1) Aprobar / rechazar / deshabilitar oferta */
  gestionarOferta(payload: GestionarOfertaPayload): Observable<GestionarOfertaResponse> {
    return this.http.put<GestionarOfertaResponse>(
      `${this.base}/gestionar-oferta.php`,
      payload,
      { withCredentials: true }
    );
  }

  /** (Rol alumno) Postularse — envía FormData con el CV */
  postularse(idOferta: number, cvFile: File): Observable<PostularseResponse> {
    const formData = new FormData();
    formData.append('id_bolsaDeTrabajo', String(idOferta));
    formData.append('cv', cvFile);
    return this.http.post<PostularseResponse>(
      `${this.base}/postularse.php`,
      formData,
      { withCredentials: true }
    );
  }
}
