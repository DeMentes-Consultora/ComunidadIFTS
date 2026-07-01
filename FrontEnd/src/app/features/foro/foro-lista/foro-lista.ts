import { ChangeDetectionStrategy, Component, inject, OnInit, OnDestroy, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { ForoCategoria, ForoTema } from '../../../shared/models/foro.model';
import { ForoService } from '../../../shared/services/foro.service';
import { ForoRealtimeService } from '../../../shared/services/foro-realtime.service';
import { AuthService } from '../../../shared/services/auth.service';
import { AuthUser } from '../../../shared/models/auth.model';
import { Subject, debounceTime, distinctUntilChanged, takeUntil } from 'rxjs';

@Component({
  selector: 'app-foro-lista',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    RouterModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatChipsModule,
    MatPaginatorModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatTooltipModule
  ],
  templateUrl: './foro-lista.html',
  styleUrl: './foro-lista.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ForoListaComponent implements OnInit, OnDestroy {
  private readonly foroService = inject(ForoService);
  private readonly realtimeService = inject(ForoRealtimeService);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly snackBar = inject(MatSnackBar);
  private readonly destroy$ = new Subject<void>();
  private readonly searchSubject = new Subject<string>();

  readonly categorias = signal<ForoCategoria[]>([]);
  readonly temas = signal<ForoTema[]>([]);
  readonly usuario = signal<AuthUser | null>(null);

  readonly cargando = signal(false);
  readonly totalTemas = signal(0);
  readonly page = signal(1);
  readonly limit = signal(15);
  readonly categoriaSeleccionada = signal<number | null>(null);
  readonly terminoBusqueda = signal('');

  ngOnInit(): void {
    this.authService.currentUser$.pipe(takeUntil(this.destroy$)).subscribe((user) => {
      this.usuario.set(user);
    });

    this.searchSubject.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      takeUntil(this.destroy$)
    ).subscribe(() => {
      this.page.set(1);
      this.cargarTemas();
    });

    this.cargarCategorias();
    this.cargarTemas();

    void this.realtimeService.ensureAnonymousSession()
      .then(() => {
        this.realtimeService.startListeningEvents();
      })
      .catch(() => {
        // El foro carga igual aunque Firebase realtime no esté disponible.
      });

    this.realtimeService.observeEventsByType('tema_creado')
      .pipe(takeUntil(this.destroy$))
      .subscribe((evento) => {
        const user = this.usuario();
        if (user && evento.id_usuario !== user.id_usuario) {
          this.snackBar.open(`${evento.usuario_nombre} creó un nuevo tema`, 'Ver', {
            duration: 5000
          }).onAction().subscribe(() => {
            this.router.navigate(['/foro/tema', evento.id_tema]);
          });
          this.cargarTemas();
        }
      });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  cargarCategorias(): void {
    this.foroService.getCategorias().subscribe({
      next: (cats) => this.categorias.set(cats),
      error: () => this.snackBar.open('Error al cargar categorías', 'Cerrar', { duration: 3000 })
    });
  }

  cargarTemas(): void {
    this.cargando.set(true);
    this.foroService.getTemas(this.page(), this.limit(), this.categoriaSeleccionada() ?? undefined, this.terminoBusqueda() || undefined)
      .subscribe({
        next: (res) => {
          this.temas.set(res.temas);
          this.totalTemas.set(res.total);
          this.cargando.set(false);
        },
        error: () => {
          this.cargando.set(false);
          this.snackBar.open('Error al cargar temas', 'Cerrar', { duration: 3000 });
        }
      });
  }

  onBusquedaChange(valor: string): void {
    this.terminoBusqueda.set(valor);
    this.searchSubject.next(valor);
  }

  filtrarPorCategoria(idCategoria: number | null): void {
    this.categoriaSeleccionada.set(idCategoria);
    this.page.set(1);
    this.cargarTemas();
  }

  onPageChange(event: PageEvent): void {
    this.page.set(event.pageIndex + 1);
    this.limit.set(event.pageSize);
    this.cargarTemas();
  }

  irACrearTema(): void {
    this.router.navigate(['/foro/crear']);
  }

  verTema(id: number): void {
    this.router.navigate(['/foro/tema', id]);
  }

  esAdmin(): boolean {
    return this.usuario()?.id_rol === 1;
  }

  formatearFecha(fecha: string): string {
    return new Date(fecha).toLocaleDateString('es-AR', {
      day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
  }

  trackById(_index: number, item: { id_tema: number }): number {
    return item.id_tema;
  }
}
