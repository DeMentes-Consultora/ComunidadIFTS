import { Component, signal, ViewChild, AfterViewInit } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { MatSidenav, MatSidenavModule } from '@angular/material/sidenav';
import { Navbar } from './layouts/navbar/navbar';
import { Sidenav } from './layouts/sidenav/sidenav';
import { Footer } from './layouts/footer/footer';
import { SidenavService } from './shared/services/sidenav.service';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, MatSidenavModule, Navbar, Sidenav, Footer],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements AfterViewInit {
  protected readonly title = signal('ComunidadIFTS');
  
  @ViewChild('sidenav') sidenav!: MatSidenav;
  
  constructor(public sidenavService: SidenavService) {}
  
  ngAfterViewInit() {
    this.sidenavService.sidenavOpen$.subscribe(isOpen => {
      if (isOpen) {
        this.sidenav.open();
      } else {
        this.sidenav.close();
      }
    });
  }
}
