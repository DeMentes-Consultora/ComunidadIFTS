import { ChangeDetectionStrategy, Component, computed, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CdkDragDrop, DragDropModule } from '@angular/cdk/drag-drop';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import {
  CarreraGestion,
  GestionCarrerasService,
  MateriaGestion,
} from '../../../shared/services/gestion-carreras.service';

@Component({
  selector: 'app-gestion-carreras',
  standalone: true,
  imports: [CommonModule, DragDropModule, MatCardModule, MatIconModule, MatProgressSpinnerModule, MatSnackBarModule],
  templateUrl: './gestion-carreras.html',
  styleUrl: './gestion-carreras.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GestionCarreras {
  private gestionCarrerasService = inject(GestionCarrerasService);
  private snackBar = inject(MatSnackBar);

  readonly cargando = signal(true);
  readonly materiasDisponibles = signal<MateriaGestion[]>([]);
  readonly carreras = signal<CarreraGestion[]>([]);

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
