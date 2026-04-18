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
    this.http.get<{ success: boolean; data: StatsPublicas }>(
      `${environment.apiUrl}/stats-publicas.php`
    ).subscribe({
      next: (res) => { if (res.success) this.stats = res.data; },
      error: () => { /* silencioso: los contadores quedan en 0 */ }
    });
  }
}
