import { Component, signal, ViewChild, AfterViewInit } from '@angular/core';
import { Router, NavigationEnd, RouterOutlet } from '@angular/router';
import { MatSidenav, MatSidenavModule, MatSidenavContent } from '@angular/material/sidenav';
import { MatDialog } from '@angular/material/dialog';
import { filter } from 'rxjs/operators';
import { Navbar } from './layouts/navbar/navbar';
import { Sidenav } from './layouts/sidenav/sidenav';
import { Footer } from './layouts/footer/footer';
import { SidenavService } from './shared/services/sidenav.service';
import { WelcomePopupComponent } from './shared/components/welcome-popup/welcome-popup.component';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, MatSidenavModule, Navbar, Sidenav, Footer],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements AfterViewInit {
  protected readonly title = signal('ComunidadIFTS');
  
  @ViewChild('sidenav') sidenav!: MatSidenav;
  @ViewChild(MatSidenavContent) sidenavContent!: MatSidenavContent;
  
  constructor(
    public sidenavService: SidenavService,
    private dialog: MatDialog,
    private router: Router
  ) {}
  
  ngAfterViewInit() {
    this.sidenavService.sidenavOpen$.subscribe(isOpen => {
      if (isOpen) {
        this.sidenav.open();
      } else {
        this.sidenav.close();
      }
    });

    this.router.events.pipe(
      filter((event): event is NavigationEnd => event instanceof NavigationEnd)
    ).subscribe(() => {
      if (this.sidenavContent) {
        this.sidenavContent.scrollTo({ top: 0 });
      }
    });

    const yaVio = sessionStorage.getItem('welcome_popup_visto');
    if (!yaVio) {
      const ref = this.dialog.open(WelcomePopupComponent, {
        panelClass: 'welcome-dialog-panel',
        width: '94%',
        maxWidth: '720px',
        autoFocus: false,
        hasBackdrop: true
      });
      ref.afterClosed().subscribe(() => {
        sessionStorage.setItem('welcome_popup_visto', 'true');
      });
    }
  }
}
