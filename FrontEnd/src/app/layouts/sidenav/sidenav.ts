import { Component } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatDividerModule } from '@angular/material/divider';
import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { SidenavService } from '../../shared/services/sidenav.service';
import { AuthService } from '../../shared/services/auth.service';
import { SiteCustomizationService } from '../../shared/services/site-customization.service';

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
}
