import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { CarruselComponent } from '../../shared/components/carrusel/carrusel';
import { MapaComponent } from '../../shared/components/mapa/mapa';
import { environment } from '../../../environments/environment';

interface StatsPublicas {
  instituciones: number;
  carreras: number;
  alumnos: number;
  ofertas_publicadas: number;
  postulantes: number;
}

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, CarruselComponent, MapaComponent],
  templateUrl: './home.html',
  styleUrl: './home.css'
})
export class Home implements OnInit {
  stats: StatsPublicas = {
    instituciones: 0,
    carreras: 0,
    alumnos: 0,
    ofertas_publicadas: 0,
    postulantes: 0,
  };

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.http.get(`${environment.apiUrl}/stats-publicas.php`, { responseType: 'text' }).subscribe({
      next: (rawRes) => {
        const res = this.parseApiResponse<StatsPublicas>(rawRes);
        if (res.success) {
          this.stats = res.data;
        }
      },
      error: () => { /* silencioso: los contadores quedan en 0 */ }
    });
  }

  private parseApiResponse<T>(rawResponse: unknown): { success: boolean; data: T } {
    if (typeof rawResponse !== 'string') {
      return rawResponse as { success: boolean; data: T };
    }

    const text = rawResponse.replace(/^\uFEFF/, '').trim();
    if (!text) {
      throw new Error('Respuesta vacia');
    }

    if (this.looksLikeInfinityFreeChallenge(text)) {
      throw new Error('Respuesta de verificacion del hosting en lugar de JSON');
    }

    const parsedDirect = this.tryParseJson<{ success: boolean; data: T }>(text);
    if (parsedDirect !== null) {
      return parsedDirect;
    }

    const preJson = this.extractJsonFromPreTag(text);
    if (preJson) {
      const parsedFromPre = this.tryParseJson<{ success: boolean; data: T }>(preJson);
      if (parsedFromPre !== null) {
        return parsedFromPre;
      }
    }

    const start = text.indexOf('{');
    const end = text.lastIndexOf('}');
    if (start !== -1 && end > start) {
      const parsedCandidate = this.tryParseJson<{ success: boolean; data: T }>(text.slice(start, end + 1));
      if (parsedCandidate !== null) {
        return parsedCandidate;
      }
    }

    throw new Error('Respuesta invalida');
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
    return lower.includes('/aes.js') || lower.includes('tonumbers(') || lower.includes('openresty');
  }

  private tryParseJson<T>(text: string): T | null {
    try {
      return JSON.parse(text) as T;
    } catch {
      return null;
    }
  }
}
