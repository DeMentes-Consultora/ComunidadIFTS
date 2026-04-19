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

export interface PerfilAlumnoPostulacion {
  id_bolsaDeTrabajo: number;
  id_postulacion?: number;
  tituloOferta: string;
  textoOferta: string;
  nombre_ifts: string;
  email_ifts: string;
  logo_ifts?: string | null;
  fecha_postulacion: string;
  cv_url?: string | null;
}

export interface PerfilAlumnoUsuario {
  id_usuario: number;
  email: string;
  id_rol: number;
  nombre_rol: string;
  id_persona: number;
  id_institucion: number;
  id_carrera?: number | null;
  anio_cursada?: number | null;
  nombre_institucion: string;
  nombre_carrera?: string | null;
  nombre: string;
  apellido: string;
  dni: string;
  telefono: string;
  edad: number;
  fecha_nacimiento: string;
  foto_perfil_url?: string | null;
}

export interface PerfilAlumnoResponse {
  success: boolean;
  message?: string;
  data: {
    usuario: PerfilAlumnoUsuario;
    postulaciones: PerfilAlumnoPostulacion[];
  };
}

export interface PerfilInstitucionData {
  id: number;
  nombre: string;
  direccion?: string | null;
  telefono?: string | null;
  email?: string | null;
  logo?: string | null;
}

export interface PerfilInstitucionResumen {
  total_ofertas_publicadas: number;
  total_postulantes: number;
}

export interface PerfilInstitucionPostulacion {
  id_postulacion: number;
  id_bolsaDeTrabajo: number;
  tituloOferta: string;
  apellido_postulante: string;
  nombre_postulante: string;
  nombre_ifts: string;
  email_postulante: string;
  cv_url?: string | null;
  foto_perfil_url?: string | null;
}

export interface PerfilInstitucionResponse {
  success: boolean;
  message?: string;
  data: {
    institucion: PerfilInstitucionData;
    puede_editar_institucion?: boolean;
    resumen: PerfilInstitucionResumen;
    postulaciones: PerfilInstitucionPostulacion[];
  };
}

export interface ActualizarInstitucionPayload {
  id: number;
  nombre: string;
  direccion?: string | null;
  telefono?: string | null;
  email?: string | null;
}

export interface ActualizarInstitucionResponse {
  success: boolean;
  message?: string;
  data?: PerfilInstitucionData;
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

  obtenerPerfilAlumno(): Observable<PerfilAlumnoResponse> {
    return this.http.get<PerfilAlumnoResponse>(
      `${this.base}/perfil-alumno.php`,
      { withCredentials: true }
    );
  }

  cancelarPostulacion(id_postulacion: number): Observable<{ success: boolean; message?: string }> {
    return this.http.post<{ success: boolean; message?: string }>(
      `${this.base}/cancelar-postulacion.php`,
      { id_postulacion },
      { withCredentials: true }
    );
  }

  actualizarDatosAcademicos(id_carrera: number, anio_cursada: number): Observable<{ success: boolean; message?: string; data?: PerfilAlumnoUsuario | null }> {
    return this.http.post<{ success: boolean; message?: string; data?: PerfilAlumnoUsuario | null }>(
      `${this.base}/actualizar-datos-academicos.php`,
      { id_carrera, anio_cursada },
      { withCredentials: true }
    );
  }

  obtenerPerfilInstitucion(): Observable<PerfilInstitucionResponse> {
    return this.http.get<PerfilInstitucionResponse>(
      `${this.base}/perfil-institucion.php`,
      { withCredentials: true }
    );
  }

  actualizarInstitucion(payload: ActualizarInstitucionPayload, logoFile?: File | null): Observable<ActualizarInstitucionResponse> {
    const formData = new FormData();
    formData.append('id', String(payload.id));
    formData.append('nombre', payload.nombre ?? '');
    formData.append('email', payload.email ?? '');
    formData.append('direccion', payload.direccion ?? '');
    formData.append('telefono', payload.telefono ?? '');

    if (logoFile) {
      formData.append('logo_file', logoFile);
    }

    return this.http.post<ActualizarInstitucionResponse>(
      `${this.base}/actualizar-institucion.php`,
      formData,
      { withCredentials: true }
    );
  }
}
