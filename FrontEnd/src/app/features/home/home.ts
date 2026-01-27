import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CarruselComponent } from '../../shared/components/carrusel/carrusel';
import { MapaComponent } from '../../shared/components/mapa/mapa';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, CarruselComponent, MapaComponent],
  templateUrl: './home.html',
  styleUrl: './home.css'
})
export class Home {}
