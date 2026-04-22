import { Component, inject } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatDividerModule } from '@angular/material/divider';
import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { MatDialog } from '@angular/material/dialog';
import { SidenavService } from '../../shared/services/sidenav.service';
import { AuthService } from '../../shared/services/auth.service';
import { SiteCustomizationService } from '../../shared/services/site-customization.service';
import { Contacto } from '../../features/contacto/contacto';

@Component({
  selector: 'app-sidenav',
  standalone: true,
  imports: [MatIconModule, MatButtonModule, MatDividerModule, RouterLink, CommonModule],
  templateUrl: './sidenav.html',
  styleUrl: './sidenav.css',
})
export class Sidenav {
  currentUser$;
  siteConfig$;
  private readonly dialog = inject(MatDialog);

  constructor(
    private sidenavService: SidenavService,
    private authService: AuthService,
    private siteCustomizationService: SiteCustomizationService
  ) {
    this.currentUser$ = this.authService.currentUser$;
    this.siteConfig$ = this.siteCustomizationService.siteConfig$;
    this.siteCustomizationService.loadPublicConfig().subscribe();
  }

  closeSidenavPanel() {
    this.sidenavService.close();
  }

  openContacto(event: Event): void {
    if (typeof window === 'undefined' || window.matchMedia('(max-width: 768px)').matches) {
      this.closeSidenavPanel();
      return;
    }

    event.preventDefault();
    this.closeSidenavPanel();
    this.dialog.open(Contacto, {
      panelClass: 'contacto-dialog-panel',
      data: { modal: true },
      maxWidth: '860px',
      width: '92vw',
      autoFocus: false,
    });
  }
}
