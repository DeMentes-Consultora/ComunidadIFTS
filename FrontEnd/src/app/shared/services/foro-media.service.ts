import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface ForoAdjuntoUpload {
  id_adjunto: number;
  tipo: string;
  url: string;
  nombre_original: string;
  tamano_bytes: number;
}

export interface ForoUploadResponse {
  success: boolean;
  message?: string;
  adjunto?: ForoAdjuntoUpload;
}

@Injectable({ providedIn: 'root' })
export class ForoMediaService {
  private readonly apiUrl = `${environment.apiUrl}/foro-adjunto-subir.php`;

  constructor(private readonly http: HttpClient) {}

  /**
   * Sube un archivo adjunto al foro.
   * @param archivo - Archivo a subir
   * @param idTema - ID del tema (opcional si es de una respuesta)
   * @param idRespuesta - ID de la respuesta (opcional si es de un tema)
   */
  subirAdjunto(archivo: File, idTema?: number | null, idRespuesta?: number | null): Observable<ForoUploadResponse> {
    const formData = new FormData();
    formData.append('archivo', archivo);

    if (idTema) formData.append('id_tema', idTema.toString());
    if (idRespuesta) formData.append('id_respuesta', idRespuesta.toString());

    return this.http.post<ForoUploadResponse>(this.apiUrl, formData);
  }

  /**
   * Valida un archivo antes de subir.
   * Retorna null si es válido, o un string con el error.
   */
  validarArchivo(archivo: File): string | null {
    const extension = archivo.name.split('.').pop()?.toLowerCase() ?? '';
    const maxImage = 300 * 1024;   // 300 KB
    const maxPdf = 300 * 1024;     // 300 KB
    const maxVideo = 500 * 1024;   // 500 KB

    const imageExts = ['jpg', 'jpeg', 'png'];
    const pdfExts = ['pdf'];
    const videoExts = ['mp4', 'webm', 'mov'];

    if (imageExts.includes(extension)) {
      if (archivo.size > maxImage) return 'Las imágenes no pueden superar 300KB.';
      return null;
    }

    if (pdfExts.includes(extension)) {
      if (archivo.size > maxPdf) return 'Los PDFs no pueden superar 300KB.';
      return null;
    }

    if (videoExts.includes(extension)) {
      if (archivo.size > maxVideo) return 'Los videos no pueden superar 500KB.';
      return null;
    }

    return 'Tipo de archivo no permitido. Solo: jpg, jpeg, png, pdf, mp4, webm, mov.';
  }

  /**
   * Obtiene el tipo de archivo para iconos/rendering.
   */
  obtenerTipoArchivo(archivo: File): 'imagen' | 'pdf' | 'video' | null {
    const ext = archivo.name.split('.').pop()?.toLowerCase() ?? '';
    if (['jpg', 'jpeg', 'png'].includes(ext)) return 'imagen';
    if (['pdf'].includes(ext)) return 'pdf';
    if (['mp4', 'webm', 'mov'].includes(ext)) return 'video';
    return null;
  }
}
