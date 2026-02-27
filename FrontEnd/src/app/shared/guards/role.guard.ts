import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, take } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';
import { AuthUser } from '../models/auth.model';

export const requireRoles = (allowedRoles: number[]): CanActivateFn => {
  return () => {
    const authService = inject(AuthService);
    const router = inject(Router);

    return authService.currentUser$.pipe(
      take(1),
      map((usuario: AuthUser | null) => {
        if (!usuario) {
          router.navigate(['/home']);
          return false;
        }

        if (allowedRoles.length === 0) {
          return true;
        }

        if (allowedRoles.includes(usuario.id_rol)) {
          return true;
        }

        router.navigate(['/home']);
        return false;
      })
    );
  };
};

export const roleGuard: CanActivateFn = (route, state) => {
  const allowedRoles = (route.data?.['roles'] as number[] | undefined) ?? [];
  return requireRoles(allowedRoles)(route, state);
};
