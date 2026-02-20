import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    redirectTo: 'home',
    pathMatch: 'full'
  },
  {
    path: 'home',
    loadComponent: () => import('./features/home/home').then(m => m.Home)
  },
  {
    path: 'mapa',
    loadComponent: () => import('./shared/components/mapa/mapa').then(m => m.MapaComponent)
  },
  {
    path: '**',
    redirectTo: 'home'
  }
];
