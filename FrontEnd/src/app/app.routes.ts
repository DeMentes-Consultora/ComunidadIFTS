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
    path: 'auth/sign-in',
    loadComponent: () => import('./features/auth/sign-in/sign-in').then(m => m.SignIn),
      // TODO: agregar GuestGuard cuando esté implementado
  },
  {
    path: 'auth/sign-up',
    loadComponent: () => import('./features/auth/sign-up/sign-up').then(m => m.SignUp),
      // TODO: agregar GuestGuard cuando esté implementado
  },
  {
    path: '**',
    redirectTo: 'home'
  }
];
