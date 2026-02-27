import { Component } from '@angular/core';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatDividerModule } from '@angular/material/divider';
import { RouterLink } from '@angular/router';
import { CommonModule } from '@angular/common';
import { SidenavService } from '../../shared/services/sidenav.service';
import { AuthService } from '../../shared/services/auth.service';

@Component({
  selector: 'app-sidenav',
  standalone: true,
  imports: [MatIconModule, MatButtonModule, MatDividerModule, RouterLink, CommonModule],
  templateUrl: './sidenav.html',
  styleUrl: './sidenav.css',
})
export class Sidenav {
  currentUser$;

  constructor(
    private sidenavService: SidenavService,
    private authService: AuthService
  ) {
    this.currentUser$ = this.authService.currentUser$;
  }

  closeSidenavPanel() {
    this.sidenavService.close();
  }
}
