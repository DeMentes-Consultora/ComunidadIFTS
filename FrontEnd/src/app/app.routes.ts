import { Routes } from '@angular/router';
import { adminGuard } from './shared/guards/admin.guard';

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
    path: 'admin/gestion-usuarios',
    loadComponent: () => import('./features/admin/gestion-usuarios/gestion-usuarios').then(m => m.GestionUsuarios),
    canActivate: [adminGuard]
  },
  {
    path: '**',
    redirectTo: 'home'
  }
];
