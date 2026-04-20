import { Component } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { CommonModule } from '@angular/common';
import { Router, RouterModule } from '@angular/router';
import { MatDialog } from '@angular/material/dialog';
import { SiteCustomizationService } from '../../shared/services/site-customization.service';
import { SidenavService } from '../../shared/services/sidenav.service';
import { AuthService } from '../../shared/services/auth.service';
import { AuthModalComponent } from '../../shared/components/auth-modal/auth-modal';

@Component({
  selector: 'app-navbar',
  standalone: true,
  imports: [MatButtonModule, MatIconModule, CommonModule, RouterModule],
  templateUrl: './navbar.html',
  styleUrl: './navbar.css',
})
export class Navbar {
  currentUser$;
  siteConfig$;

  constructor(
    private siteCustomizationService: SiteCustomizationService,
    private sidenavService: SidenavService,
    private authService: AuthService,
    private router: Router,
    private dialog: MatDialog
  ) {
    this.currentUser$ = this.authService.currentUser$;
    this.siteConfig$ = this.siteCustomizationService.siteConfig$;
    this.siteCustomizationService.loadPublicConfig().subscribe();
  }

  openSidenav() {
    this.sidenavService.toggle();
  }

  cerrarSesion(): void {
    this.authService.logout().subscribe({
      next: () => this.router.navigate(['/home']),
      error: () => this.router.navigate(['/home'])
    });
  }

  abrirModalAuth(view: 'login' | 'register' = 'login'): void {
    this.dialog.open(AuthModalComponent, {
      disableClose: true,
      width: '92%',
      maxWidth: '600px',
      panelClass: 'auth-dialog-panel',
      data: { view }
    });
  }

  getAvatarText(nombre?: string | null): string {
    const cleanName = (nombre ?? '').trim();
    return cleanName ? cleanName.charAt(0).toUpperCase() : '?';
  }

  getAvatarUrl(user: { foto_perfil_url?: string | null; logo_ifts?: string | null } | null | undefined): string | null {
    const fotoPerfil = (user?.foto_perfil_url ?? '').trim();
    if (fotoPerfil) {
      return fotoPerfil;
    }

    const logoInstitucion = (user?.logo_ifts ?? '').trim();
    return logoInstitucion || null;
  }
}
