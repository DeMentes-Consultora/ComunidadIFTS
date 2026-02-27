import { Routes } from '@angular/router';
import { authGuard, roleGuard } from './shared/guards';

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
    path: 'admin',
    canActivate: [authGuard, roleGuard],
    data: { roles: [1] },
    children: [
      {
        path: 'gestion-usuarios',
        loadComponent: () => import('./features/admin/gestion-usuarios/gestion-usuarios').then(m => m.GestionUsuarios)
      },
      {
        path: '',
        redirectTo: 'gestion-usuarios',
        pathMatch: 'full'
      }
    ]
  },
  {
    path: '**',
    redirectTo: 'home'
  }
];
