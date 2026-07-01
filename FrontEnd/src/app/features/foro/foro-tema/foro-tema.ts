import { ChangeDetectionStrategy, Component, inject, OnInit, OnDestroy, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialog } from '@angular/material/dialog';
import { MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { ForoTema, ForoRespuesta, ForoAdjunto } from '../../../shared/models/foro.model';
import { ForoService } from '../../../shared/services/foro.service';
import { ForoMediaService } from '../../../shared/services/foro-media.service';
import { ForoRealtimeService, ForoEvent } from '../../../shared/services/foro-realtime.service';
import { ForoImagenPreviewDialogComponent } from '../foro-imagen-preview-dialog';
import { AuthService } from '../../../shared/services/auth.service';
import { AuthUser } from '../../../shared/models/auth.model';
import { Subject, takeUntil } from 'rxjs';

@Component({
  selector: 'app-foro-tema',
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
    MatChipsModule,
    MatPaginatorModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatTooltipModule
  ],
  templateUrl: './foro-tema.html',
  styleUrl: './foro-tema.css',
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ForoTemaComponent implements OnInit, OnDestroy {
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly foroService = inject(ForoService);
  private readonly mediaService = inject(ForoMediaService);
  private readonly realtimeService = inject(ForoRealtimeService);
  private readonly dialog = inject(MatDialog);
  private readonly authService = inject(AuthService);
  private readonly fb = inject(FormBuilder);
  private readonly snackBar = inject(MatSnackBar);
  private readonly destroy$ = new Subject<void>();

  readonly tema = signal<ForoTema | null>(null);
  readonly adjuntosTema = signal<ForoAdjunto[]>([]);
  readonly respuestas = signal<ForoRespuesta[]>([]);
  readonly usuario = signal<AuthUser | null>(null);

  readonly cargando = signal(true);
  readonly cargandoRespuestas = signal(false);
  readonly totalRespuestas = signal(0);
  readonly pageRespuestas = signal(1);
  readonly limitRespuestas = signal(20);

  readonly respuestaForm: FormGroup = this.fb.group({
    contenido: ['', [Validators.required]]
  });

  readonly respuestaEditandoId = signal<number | null>(null);
  readonly respuestaEditandoContenido = signal('');
  readonly citandoId = signal<number | null>(null);
  readonly citandoTexto = signal('');

  readonly archivosAdjuntos = signal<File[]>([]);
  readonly archivosError = signal<string[]>([]);
  readonly enviando = signal(false);

  private temaId = 0;

  ngOnInit(): void {
    this.authService.currentUser$.pipe(takeUntil(this.destroy$)).subscribe((user) => {
      this.usuario.set(user);
    });

    this.temaId = Number(this.route.snapshot.paramMap.get('id'));
    if (this.temaId <= 0) {
      this.router.navigate(['/foro']);
      return;
    }

    this.cargarTema();

    void this.realtimeService.ensureAnonymousSession()
      .then(() => {
        this.realtimeService.startListeningEvents();

        this.realtimeService.observeEventsByTema(this.temaId)
          .pipe(takeUntil(this.destroy$))
          .subscribe((evento) => {
            const user = this.usuario();
            if (user && evento.id_usuario !== user.id_usuario) {
              this.handleRealtimeEvent(evento);
            }
          });

        const user = this.usuario();
        if (user) {
          this.realtimeService.startTopicPresence(user, this.temaId);
        }
      })
      .catch(() => {
        // El tema carga igual aunque Firebase realtime no esté disponible.
      });
  }

  ngOnDestroy(): void {
    const user = this.usuario();
    if (user && this.temaId) {
      this.realtimeService.markOffline(user);
    }
    this.destroy$.next();
    this.destroy$.complete();
  }

  private handleRealtimeEvent(evento: ForoEvent): void {
    switch (evento.type) {
      case 'respuesta_creada':
        this.snackBar.open(`${evento.usuario_nombre} publicó una respuesta`, 'Ver', { duration: 4000 })
          .onAction().subscribe(() => this.cargarRespuestas());
        this.cargarRespuestas();
        this.tema.update(t => t ? { ...t, cantidad_respuestas: t.cantidad_respuestas + 1 } : t);
        break;
      case 'respuesta_editada':
        this.cargarRespuestas();
        break;
      case 'respuesta_eliminada':
        this.cargarRespuestas();
        this.tema.update(t => t ? { ...t, cantidad_respuestas: Math.max(0, t.cantidad_respuestas - 1) } : t);
        break;
      case 'tema_cerrado':
        this.cargarTema();
        this.snackBar.open('El tema fue cerrado', 'Cerrar', { duration: 3000 });
        break;
      case 'tema_abierto':
        this.cargarTema();
        this.snackBar.open('El tema fue abierto', 'Cerrar', { duration: 3000 });
        break;
      case 'tema_fijado':
        this.cargarTema();
        break;
      case 'tema_desfijado':
        this.cargarTema();
        break;
    }
  }

  abrirVistaImagen(url: string, title: string): void {
    this.dialog.open(ForoImagenPreviewDialogComponent, {
      data: { url, title },
      maxWidth: '95vw',
      maxHeight: '95vh',
      autoFocus: false,
      panelClass: 'foro-image-preview-dialog'
    });
  }

  cargarTema(): void {
    this.cargando.set(true);
    this.foroService.getTema(this.temaId).subscribe({
      next: (res) => {
        this.tema.set(res.tema);
        this.adjuntosTema.set(res.adjuntos);
        this.cargando.set(false);
        this.cargarRespuestas();
      },
      error: () => {
        this.cargando.set(false);
        this.snackBar.open('Tema no encontrado', 'Cerrar', { duration: 3000 });
        this.router.navigate(['/foro']);
      }
    });
  }

  cargarRespuestas(): void {
    this.cargandoRespuestas.set(true);
    this.foroService.getRespuestas(this.temaId, this.pageRespuestas(), this.limitRespuestas())
      .subscribe({
        next: (res) => {
          this.respuestas.set(res.respuestas);
          this.totalRespuestas.set(res.total);
          this.cargandoRespuestas.set(false);
        },
        error: () => {
          this.cargandoRespuestas.set(false);
        }
      });
  }

  onPageRespuestasChange(event: PageEvent): void {
    this.pageRespuestas.set(event.pageIndex + 1);
    this.limitRespuestas.set(event.pageSize);
    this.cargarRespuestas();
  }

  // ------------------------------------------------------------------
  // RESPUESTAS
  // ------------------------------------------------------------------

  enviarRespuesta(): void {
    if (this.respuestaForm.invalid || this.enviando()) return;

    const currentTema = this.tema();
    if (!currentTema) return;

    if (currentTema.cerrado) {
      this.snackBar.open('Este tema está cerrado y no admite respuestas', 'Cerrar', { duration: 3000 });
      return;
    }

    this.enviando.set(true);
    const contenido = String(this.respuestaForm.value.contenido ?? '').trim();

    this.foroService.crearRespuesta({
      id_tema: this.temaId,
      contenido,
      citando_id: this.citandoId()
    }).subscribe({
      next: async (res) => {
        const idRespuesta = res.id_respuesta;

        const archivos = this.archivosAdjuntos();
        if (archivos.length > 0) {
          for (const archivo of archivos) {
            await this.mediaService.subirAdjunto(archivo, null, idRespuesta).toPromise();
          }
        }

        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.publishEvent('respuesta_creada', this.temaId, user, { id_respuesta: idRespuesta });
          } catch {
            // Silenciar error de Firebase
          }
        }

        this.respuestaForm.reset();
        this.respuestaForm.patchValue({ contenido: '' });
        this.archivosAdjuntos.set([]);
        this.archivosError.set([]);
        this.citandoId.set(null);
        this.citandoTexto.set('');
        this.enviando.set(false);
        this.snackBar.open('Respuesta publicada', 'Cerrar', { duration: 3000 });
        this.cargarRespuestas();
        this.tema.update(t => t ? { ...t, cantidad_respuestas: t.cantidad_respuestas + 1 } : t);
      },
      error: (err) => {
        this.enviando.set(false);
        this.snackBar.open(err.error?.message || 'Error al enviar respuesta', 'Cerrar', { duration: 3000 });
      }
    });
  }

  // ------------------------------------------------------------------
  // CITAR / EDITAR / ELIMINAR
  // ------------------------------------------------------------------

  citarRespuesta(respuesta: ForoRespuesta): void {
    this.citandoId.set(respuesta.id_respuesta);
    this.citandoTexto.set(`"${respuesta.contenido.substring(0, 80)}..." — ${respuesta.autor_nombre} ${respuesta.autor_apellido}`);
  }

  cancelarCita(): void {
    this.citandoId.set(null);
    this.citandoTexto.set('');
  }

  iniciarEdicionRespuesta(respuesta: ForoRespuesta): void {
    this.respuestaEditandoId.set(respuesta.id_respuesta);
    this.respuestaEditandoContenido.set(respuesta.contenido);
  }

  cancelarEdicionRespuesta(): void {
    this.respuestaEditandoId.set(null);
    this.respuestaEditandoContenido.set('');
  }

  guardarEdicionRespuesta(): void {
    const editId = this.respuestaEditandoId();
    if (!editId || this.respuestaEditandoContenido().trim() === '') return;

    this.foroService.actualizarRespuesta({
      id_respuesta: editId,
      contenido: this.respuestaEditandoContenido().trim()
    }).subscribe({
      next: async () => {
        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.publishEvent('respuesta_editada', this.temaId, user, { id_respuesta: editId });
          } catch {
            // Silenciar error de Firebase
          }
        }

        this.snackBar.open('Respuesta actualizada', 'Cerrar', { duration: 3000 });
        this.cancelarEdicionRespuesta();
        this.cargarRespuestas();
      },
      error: (err) => {
        this.snackBar.open(err.error?.message || 'Error al editar', 'Cerrar', { duration: 3000 });
      }
    });
  }

  eliminarRespuesta(idRespuesta: number): void {
    if (!confirm('¿Eliminar esta respuesta?')) return;

    this.foroService.eliminarRespuesta(idRespuesta).subscribe({
      next: async () => {
        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.publishEvent('respuesta_eliminada', this.temaId, user, { id_respuesta: idRespuesta });
          } catch {
            // Silenciar error de Firebase
          }
        }

        this.snackBar.open('Respuesta eliminada', 'Cerrar', { duration: 3000 });
        this.tema.update(t => t ? { ...t, cantidad_respuestas: Math.max(0, t.cantidad_respuestas - 1) } : t);
        this.cargarRespuestas();
      },
      error: (err) => {
        this.snackBar.open(err.error?.message || 'Error al eliminar', 'Cerrar', { duration: 3000 });
      }
    });
  }

  // ------------------------------------------------------------------
  // ACCIONES DEL TEMA
  // ------------------------------------------------------------------

  cerrarTema(): void {
    if (!confirm('¿Cerrar este tema?')) return;

    this.foroService.accionTema(this.temaId, 'cerrar', { motivo: 'Cerrado por el administrador' }).subscribe({
      next: async () => {
        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.publishEvent('tema_cerrado', this.temaId, user);
          } catch {
            // Silenciar error de Firebase
          }
        }

        this.snackBar.open('Tema cerrado', 'Cerrar', { duration: 3000 });
        this.cargarTema();
      }
    });
  }

  abrirTema(): void {
    this.foroService.accionTema(this.temaId, 'abrir').subscribe({
      next: async () => {
        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.publishEvent('tema_abierto', this.temaId, user);
          } catch {
            // Silenciar error de Firebase
          }
        }

        this.snackBar.open('Tema abierto', 'Cerrar', { duration: 3000 });
        this.cargarTema();
      }
    });
  }

  fijarTema(fijar: boolean): void {
    this.foroService.accionTema(this.temaId, 'fijar', { fijar }).subscribe({
      next: async () => {
        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.publishEvent(fijar ? 'tema_fijado' : 'tema_desfijado', this.temaId, user);
          } catch {
            // Silenciar error de Firebase
          }
        }

        this.snackBar.open(fijar ? 'Tema fijado' : 'Tema desfijado', 'Cerrar', { duration: 3000 });
        this.cargarTema();
      }
    });
  }

  eliminarTema(): void {
    if (!confirm('¿Eliminar este tema permanentemente?')) return;

    this.foroService.eliminarTema(this.temaId).subscribe({
      next: async () => {
        const user = this.usuario();
        if (user) {
          try {
            await this.realtimeService.publishEvent('tema_eliminado', this.temaId, user);
          } catch {
            // Silenciar error de Firebase
          }
        }

        this.snackBar.open('Tema eliminado', 'Cerrar', { duration: 3000 });
        this.router.navigate(['/foro']);
      }
    });
  }

  // ------------------------------------------------------------------
  // ARCHIVOS
  // ------------------------------------------------------------------

  onArchivosSeleccionados(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files) return;

    this.archivosError.set([]);
    const nuevos: File[] = [];

    Array.from(input.files).forEach((file) => {
      const error = this.mediaService.validarArchivo(file);
      if (error) {
        this.archivosError.update(errors => [...errors, `${file.name}: ${error}`]);
      } else {
        nuevos.push(file);
      }
    });

    this.archivosAdjuntos.update(prev => [...prev, ...nuevos]);
    input.value = '';
  }

  eliminarArchivo(index: number): void {
    this.archivosAdjuntos.update(prev => prev.filter((_, i) => i !== index));
  }

  // ------------------------------------------------------------------
  // HELPERS
  // ------------------------------------------------------------------

  esAdmin(): boolean {
    return this.usuario()?.id_rol === 1;
  }

  esAutorTema(): boolean {
    const user = this.usuario();
    const currentTema = this.tema();
    return !!user && !!currentTema && user.id_usuario === currentTema.id_usuario;
  }

  esAutorRespuesta(respuesta: ForoRespuesta): boolean {
    const user = this.usuario();
    return !!user && user.id_usuario === respuesta.id_usuario;
  }

  formatearFecha(fecha: string): string {
    return new Date(fecha).toLocaleDateString('es-AR', {
      day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
  }

  trackById(_index: number, item: { id_respuesta: number }): number {
    return item.id_respuesta;
  }

  irALista(): void {
    this.router.navigate(['/foro']);
  }
}
