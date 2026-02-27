import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, take } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';
import { AuthUser } from '../models/auth.model';

export const authGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return authService.currentUser$.pipe(
    take(1),
    map((usuario: AuthUser | null) => {
      if (usuario) {
        return true;
      }

      router.navigate(['/home']);
      return false;
    })
  );
};
