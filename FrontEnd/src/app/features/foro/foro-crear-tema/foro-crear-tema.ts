import { ChangeDetectionStrategy, Component, inject, OnInit, OnDestroy, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router, RouterModule } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatChipsModule } from '@angular/material/chips';
import { ForoCategoria } from '../../../shared/models/foro.model';
import { ForoService } from '../../../shared/services/foro.service';
import { ForoMediaService } from '../../../shared/services/foro-media.service';
import { ForoRealtimeService } from '../../../shared/services/foro-realtime.service';
import { AuthService } from '../../../shared/services/auth.service';
import { AuthUser } from '../../../shared/models/auth.model';
import { Subject, takeUntil } from 'rxjs';

@Component({
  selector: 'app-foro-crear-tema',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatChipsModule
  ],
  templateUrl: './foro-crear-tema.html',
  styleUrl: './foro-crear-tema.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ForoCrearTemaComponent implements OnInit, OnDestroy {
  private readonly foroService = inject(ForoService);
  private readonly mediaService = inject(ForoMediaService);
  private readonly realtimeService = inject(ForoRealtimeService);
  private readonly authService = inject(AuthService);
  private readonly fb = inject(FormBuilder);
  private readonly router = inject(Router);
  private readonly snackBar = inject(MatSnackBar);
  private readonly destroy$ = new Subject<void>();

  readonly categorias = signal<ForoCategoria[]>([]);
  readonly archivosAdjuntos = signal<File[]>([]);
  readonly archivosError = signal<string[]>([]);
  readonly creando = signal(false);
  readonly usuario = signal<AuthUser | null>(null);

  readonly form: FormGroup = this.fb.group({
    id_categoria: [null, Validators.required],
    titulo: ['', [Validators.required, Validators.maxLength(255)]],
    contenido: ['', [Validators.required]]
  });

  ngOnInit(): void {
    this.authService.currentUser$.pipe(takeUntil(this.destroy$)).subscribe((user) => {
      this.usuario.set(user);
    });

    this.foroService.getCategorias().pipe(takeUntil(this.destroy$)).subscribe({
      next: (cats) => this.categorias.set(cats),
      error: () => this.snackBar.open('Error al cargar categorías', 'Cerrar', { duration: 3000 })
    });
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  onArchivosSeleccionados(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;

    this.archivosError.set([]);
    const nuevosArchivos: File[] = [];

    Array.from(input.files).forEach((file) => {
      const error = this.mediaService.validarArchivo(file);
      if (error) {
        this.archivosError.update(errors => [...errors, `${file.name}: ${error}`]);
      } else {
        nuevosArchivos.push(file);
      }
    });

    this.archivosAdjuntos.update(prev => [...prev, ...nuevosArchivos]);
    input.value = '';
  }

  eliminarArchivo(index: number): void {
    this.archivosAdjuntos.update(prev => prev.filter((_, i) => i !== index));
  }

  async crearTema(): Promise<void> {
    if (this.form.invalid || this.creando()) return;

    this.creando.set(true);
    const datos = this.form.value;

    this.foroService.crearTema(datos).subscribe({
      next: async (res) => {
        const idTema = res.id_tema;

        const archivos = this.archivosAdjuntos();
        if (archivos.length > 0) {
          for (const archivo of archivos) {
            await this.mediaService.subirAdjunto(archivo, idTema).toPromise();
          }
        }

        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.ensureAnonymousSession();
            await this.realtimeService.publishEvent('tema_creado', idTema, user);
          } catch {
            // Silenciar error de Firebase, el tema ya se creó en MySQL
          }
        }

        this.snackBar.open('Tema creado correctamente', 'Cerrar', { duration: 3000 });
        this.router.navigate(['/foro/tema', idTema]);
      },
      error: (err) => {
        this.creando.set(false);
        this.snackBar.open(err.error?.message || 'Error al crear el tema', 'Cerrar', { duration: 3000 });
      }
    });
  }

  cancelar(): void {
    this.router.navigate(['/foro']);
  }
}
