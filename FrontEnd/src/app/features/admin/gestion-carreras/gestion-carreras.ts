import { ChangeDetectionStrategy, Component, computed, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CdkDragDrop, DragDropModule } from '@angular/cdk/drag-drop';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { FormsModule } from '@angular/forms';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import {
  CarreraGestion,
  GestionCarrerasService,
  MateriaGestion,
} from '../../../shared/services/gestion-carreras.service';
import { ConfirmDialogComponent } from '../../../shared/components/confirm-dialog/confirm-dialog';

@Component({
  selector: 'app-gestion-carreras',
  standalone: true,
  imports: [
    CommonModule,
    DragDropModule,
    MatCardModule,
    MatIconModule,
    MatButtonModule,
    MatFormFieldModule,
    MatInputModule,
    FormsModule,
    MatDialogModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
  ],
  templateUrl: './gestion-carreras.html',
  styleUrl: './gestion-carreras.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GestionCarreras {
  private gestionCarrerasService = inject(GestionCarrerasService);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);

  readonly cargando = signal(true);
  readonly materiasDisponibles = signal<MateriaGestion[]>([]);
  readonly carreras = signal<CarreraGestion[]>([]);
  readonly mostrarFormularioCarrera = signal(false);
  readonly mostrarFormularioMateria = signal(false);
  readonly carreraEnEdicion = signal<CarreraGestion | null>(null);
  readonly materiaEnEdicion = signal<MateriaGestion | null>(null);

  nuevaCarreraNombre = '';
  nuevaMateriaNombre = '';
  carreraEditadaNombre = '';
  materiaEditadaNombre = '';

  readonly dropListIds = computed(() => [
    'materias-disponibles',
    ...this.carreras().map((c) => `carrera-${c.id_carrera}`),
  ]);

  constructor() {
    this.cargarEstado();
  }

  cargarEstado(): void {
    this.cargando.set(true);

    this.gestionCarrerasService.obtenerEstado().subscribe({
      next: (response) => {
        if (response.success && response.data) {
          this.materiasDisponibles.set(response.data.materias || []);
          this.carreras.set(response.data.carreras || []);
        } else {
          this.mostrarMensaje(response.message || 'No se pudo cargar la gestión de carreras', 'error');
        }
        this.cargando.set(false);
      },
      error: (err) => {
        const message = err?.error?.message || 'Error al cargar la gestión de carreras';
        this.mostrarMensaje(message, 'error');
        this.cargando.set(false);
      },
    });
  }

  toggleFormularioCarrera(): void {
    const nuevoEstado = !this.mostrarFormularioCarrera();
    this.mostrarFormularioCarrera.set(nuevoEstado);
    if (!nuevoEstado) {
      this.nuevaCarreraNombre = '';
    }
  }

  toggleFormularioMateria(): void {
    const nuevoEstado = !this.mostrarFormularioMateria();
    this.mostrarFormularioMateria.set(nuevoEstado);
    if (!nuevoEstado) {
      this.nuevaMateriaNombre = '';
    }
  }

  crearCarrera(): void {
    const nombre = this.nuevaCarreraNombre.trim();
    if (!nombre) {
      this.mostrarMensaje('Ingresá el nombre de la carrera', 'error');
      return;
    }

    this.gestionCarrerasService.crearCarrera(nombre).subscribe({
      next: (response) => {
        if (response.success) {
          this.mostrarMensaje('Carrera creada correctamente', 'success');
          this.nuevaCarreraNombre = '';
          this.mostrarFormularioCarrera.set(false);
          this.cargarEstado();
          return;
        }

        this.mostrarMensaje(response.message || 'No se pudo crear la carrera', 'error');
      },
      error: (err) => {
        const message = err?.error?.message || 'Error al crear carrera';
        this.mostrarMensaje(message, 'error');
      },
    });
  }

  crearMateria(): void {
    const nombre = this.nuevaMateriaNombre.trim();
    if (!nombre) {
      this.mostrarMensaje('Ingresá el nombre de la materia', 'error');
      return;
    }

    this.gestionCarrerasService.crearMateria(nombre).subscribe({
      next: (response) => {
        if (response.success) {
          this.mostrarMensaje('Materia creada correctamente', 'success');
          this.nuevaMateriaNombre = '';
          this.mostrarFormularioMateria.set(false);
          this.cargarEstado();
          return;
        }

        this.mostrarMensaje(response.message || 'No se pudo crear la materia', 'error');
      },
      error: (err) => {
        const message = err?.error?.message || 'Error al crear materia';
        this.mostrarMensaje(message, 'error');
      },
    });
  }

  iniciarEdicionCarrera(carrera: CarreraGestion): void {
    this.carreraEnEdicion.set(carrera);
    this.carreraEditadaNombre = carrera.nombre_carrera;
  }

  cancelarEdicionCarrera(): void {
    this.carreraEnEdicion.set(null);
    this.carreraEditadaNombre = '';
  }

  guardarEdicionCarrera(): void {
    const carrera = this.carreraEnEdicion();
    if (!carrera) {
      return;
    }

    const nombre = this.carreraEditadaNombre.trim();
    if (!nombre) {
      this.mostrarMensaje('Ingresá el nombre de la carrera', 'error');
      return;
    }

    this.gestionCarrerasService.editarCarrera(carrera.id_carrera, nombre).subscribe({
      next: (response) => {
        if (response.success) {
          this.mostrarMensaje('Carrera actualizada correctamente', 'success');
          this.cancelarEdicionCarrera();
          this.cargarEstado();
          return;
        }

        this.mostrarMensaje(response.message || 'No se pudo actualizar la carrera', 'error');
      },
      error: (err) => {
        const message = err?.error?.message || 'Error al actualizar carrera';
        this.mostrarMensaje(message, 'error');
      },
    });
  }

  eliminarCarrera(carrera: CarreraGestion): void {
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      width: '420px',
      data: {
        title: 'Eliminar carrera',
        message: `¿Eliminar la carrera ${carrera.nombre_carrera}?`,
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
      },
    });

    dialogRef.afterClosed().subscribe((confirmado: boolean) => {
      if (!confirmado) {
        return;
      }

      this.gestionCarrerasService.eliminarCarrera(carrera.id_carrera).subscribe({
        next: (response) => {
          if (response.success) {
            this.mostrarMensaje('Carrera eliminada correctamente', 'success');
            this.cancelarEdicionCarrera();
            this.cargarEstado();
            return;
          }

          this.mostrarMensaje(response.message || 'No se pudo eliminar la carrera', 'error');
        },
        error: (err) => {
          const message = err?.error?.message || 'Error al eliminar carrera';
          this.mostrarMensaje(message, 'error');
        },
      });
    });
  }

  iniciarEdicionMateria(materia: MateriaGestion): void {
    this.materiaEnEdicion.set(materia);
    this.materiaEditadaNombre = materia.nombre_materia;
  }

  cancelarEdicionMateria(): void {
    this.materiaEnEdicion.set(null);
    this.materiaEditadaNombre = '';
  }

  guardarEdicionMateria(): void {
    const materia = this.materiaEnEdicion();
    if (!materia) {
      return;
    }

    const nombre = this.materiaEditadaNombre.trim();
    if (!nombre) {
      this.mostrarMensaje('Ingresá el nombre de la materia', 'error');
      return;
    }

    this.gestionCarrerasService.editarMateria(materia.id_materia, nombre).subscribe({
      next: (response) => {
        if (response.success) {
          this.mostrarMensaje('Materia actualizada correctamente', 'success');
          this.cancelarEdicionMateria();
          this.cargarEstado();
          return;
        }

        this.mostrarMensaje(response.message || 'No se pudo actualizar la materia', 'error');
      },
      error: (err) => {
        const message = err?.error?.message || 'Error al actualizar materia';
        this.mostrarMensaje(message, 'error');
      },
    });
  }

  eliminarMateria(materia: MateriaGestion): void {
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      width: '420px',
      data: {
        title: 'Eliminar materia',
        message: `¿Eliminar la materia ${materia.nombre_materia}?`,
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
      },
    });

    dialogRef.afterClosed().subscribe((confirmado: boolean) => {
      if (!confirmado) {
        return;
      }

      this.gestionCarrerasService.eliminarMateria(materia.id_materia).subscribe({
        next: (response) => {
          if (response.success) {
            this.mostrarMensaje('Materia eliminada correctamente', 'success');
            this.cancelarEdicionMateria();
            this.cargarEstado();
            return;
          }

          this.mostrarMensaje(response.message || 'No se pudo eliminar la materia', 'error');
        },
        error: (err) => {
          const message = err?.error?.message || 'Error al eliminar materia';
          this.mostrarMensaje(message, 'error');
        },
      });
    });
  }

  dropMateria(event: CdkDragDrop<MateriaGestion[]>, carreraDestino?: CarreraGestion): void {
    if (event.previousContainer === event.container) {
      return;
    }

    const materia = event.previousContainer.data[event.previousIndex];
    if (!materia) {
      return;
    }

    if (event.previousContainer.id === 'materias-disponibles' && carreraDestino) {
      this.gestionCarrerasService.asociarMateria(carreraDestino.id_carrera, materia.id_materia).subscribe({
        next: (response) => {
          if (response.success) {
            this.mostrarMensaje('Materia asociada correctamente', 'success');
            this.cargarEstado();
            return;
          }

          this.mostrarMensaje(response.message || 'No se pudo asociar la materia', 'error');
          this.cargarEstado();
        },
        error: (err) => {
          const message = err?.error?.message || 'Error al asociar materia';
          this.mostrarMensaje(message, 'error');
          this.cargarEstado();
        },
      });
      return;
    }

    if (event.container.id === 'materias-disponibles') {
      const idCarreraOrigen = this.obtenerIdCarreraDesdeDropListId(event.previousContainer.id);
      if (!idCarreraOrigen) {
        return;
      }

      this.gestionCarrerasService.desasociarMateria(idCarreraOrigen, materia.id_materia).subscribe({
        next: (response) => {
          if (response.success) {
            this.mostrarMensaje('Materia desasociada correctamente', 'success');
            this.cargarEstado();
            return;
          }

          this.mostrarMensaje(response.message || 'No se pudo desasociar la materia', 'error');
          this.cargarEstado();
        },
        error: (err) => {
          const message = err?.error?.message || 'Error al desasociar materia';
          this.mostrarMensaje(message, 'error');
          this.cargarEstado();
        },
      });
      return;
    }

    const idCarreraOrigen = this.obtenerIdCarreraDesdeDropListId(event.previousContainer.id);
    const idCarreraDestino = this.obtenerIdCarreraDesdeDropListId(event.container.id);

    if (!idCarreraOrigen || !idCarreraDestino || idCarreraOrigen === idCarreraDestino) {
      this.cargarEstado();
      return;
    }

    this.gestionCarrerasService.desasociarMateria(idCarreraOrigen, materia.id_materia).subscribe({
      next: (responseDesasociar) => {
        if (!responseDesasociar.success) {
          this.mostrarMensaje(responseDesasociar.message || 'No se pudo mover la materia', 'error');
          this.cargarEstado();
          return;
        }

        this.gestionCarrerasService.asociarMateria(idCarreraDestino, materia.id_materia).subscribe({
          next: (responseAsociar) => {
            if (responseAsociar.success) {
              this.mostrarMensaje('Materia movida correctamente', 'success');
            } else {
              this.mostrarMensaje(responseAsociar.message || 'No se pudo mover la materia', 'error');
            }
            this.cargarEstado();
          },
          error: (err) => {
            const message = err?.error?.message || 'Error al mover materia';
            this.mostrarMensaje(message, 'error');
            this.cargarEstado();
          },
        });
      },
      error: (err) => {
        const message = err?.error?.message || 'Error al mover materia';
        this.mostrarMensaje(message, 'error');
        this.cargarEstado();
      },
    });
  }

  private obtenerIdCarreraDesdeDropListId(dropListId: string): number | null {
    if (!dropListId.startsWith('carrera-')) {
      return null;
    }

    const id = Number(dropListId.replace('carrera-', ''));
    return Number.isFinite(id) ? id : null;
  }

  private mostrarMensaje(mensaje: string, tipo: 'success' | 'error'): void {
    this.snackBar.open(mensaje, 'Cerrar', {
      duration: 3000,
      horizontalPosition: 'center',
      verticalPosition: 'top',
      panelClass: tipo === 'success' ? 'snackbar-success' : 'snackbar-error',
    });
  }
}
