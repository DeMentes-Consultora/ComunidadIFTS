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
        path: 'dashboard',
        loadComponent: () => import('./features/admin/dashboard/dashboard').then(m => m.AdminDashboard)
      },
      {
        path: 'gestion-usuarios',
        loadComponent: () => import('./features/admin/gestion-usuarios/gestion-usuarios').then(m => m.GestionUsuarios)
      },
      {
        path: 'gestion-instituciones',
        loadComponent: () => import('./features/admin/gestion-instituciones/gestion-instituciones').then(m => m.GestionInstituciones)
      },
      {
        path: 'gestion-carreras',
        loadComponent: () => import('./features/admin/gestion-carreras/gestion-carreras').then(m => m.GestionCarreras)
      },
      {
        path: 'gestion-ofertas',
        loadComponent: () => import('./features/admin/gestion-ofertas/gestion-ofertas').then(m => m.GestionOfertas)
      },
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      }
    ]
  },
  {
    path: 'bolsa-trabajo',
    canActivate: [authGuard, roleGuard],
    data: { roles: [2] },
    loadComponent: () => import('./features/bolsa-trabajo/bolsa-trabajo').then(m => m.BolsaTrabajo)
  },
  {
    path: 'perfil',
    canActivate: [authGuard, roleGuard],
    data: { roles: [2] },
    loadComponent: () => import('./features/perfil-alumno/perfil-alumno').then(m => m.PerfilAlumno)
  },
  {
    path: 'crear-oferta',
    canActivate: [authGuard, roleGuard],
    data: { roles: [3] },
    loadComponent: () => import('./features/bolsa-trabajo/crear-oferta/crear-oferta').then(m => m.CrearOferta)
  },
  {
    path: '**',
    redirectTo: 'home'
  }
];
