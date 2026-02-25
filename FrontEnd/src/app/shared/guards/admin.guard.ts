import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from '../services/auth.service';
import { map, take } from 'rxjs/operators';
import { AuthUser } from '../models/auth.model';

/**
 * Guard para proteger rutas de administrador
 * Solo permite acceso a usuarios con rol ID 1 (AdministradorComunidad)
 */
export const adminGuard: CanActivateFn = (route, state) => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return authService.currentUser$.pipe(
    take(1),
    map((usuario: AuthUser | null) => {
      if (!usuario) {
        // No autenticado - redirigir al home
        router.navigate(['/home']);
        return false;
      }

      // Verificar que sea administrador (ID 1)
      if (usuario.id_rol === 1) {
        return true;
      }

      // No es administrador - redirigir al home
      alert('No tienes permisos para acceder a esta sección. Solo administradores (rol ID 1).');
      router.navigate(['/home']);
      return false;
    })
  );
};
