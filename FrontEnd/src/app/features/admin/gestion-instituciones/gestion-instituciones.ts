import { ChangeDetectionStrategy, Component, computed, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTableModule } from '@angular/material/table';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { InstitucionesService } from '../../../shared/services/instituciones.service';
import { Institucion } from '../../../shared/models/institucion.model';
import { FormularioInstitucionComponent } from '../../../shared/components/formulario-institucion/formulario-institucion';

@Component({
  selector: 'app-gestion-instituciones',
  standalone: true,
  imports: [
    CommonModule,
    MatTableModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatDialogModule,
  ],
  templateUrl: './gestion-instituciones.html',
  styleUrl: './gestion-instituciones.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class GestionInstituciones {
  private institucionesService = inject(InstitucionesService);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);

  readonly instituciones = signal<Institucion[]>([]);
  readonly cargando = signal(true);
  readonly displayedColumns = ['id_institucion', 'logo_institucion', 'nombre_institucion', 'direccion_institucion', 'acciones'];

  readonly institucionesOrdenadas = computed(() =>
    [...this.instituciones()].sort((a, b) => a.id - b.id)
  );

  constructor() {
    this.cargarInstituciones();
  }

  cargarInstituciones(): void {
    this.cargando.set(true);

    this.institucionesService.obtenerTodas().subscribe({
      next: (data) => {
        this.instituciones.set(data);
        this.cargando.set(false);
      },
      error: () => {
        this.cargando.set(false);
        this.mostrarMensaje('Error al cargar instituciones', 'error');
      },
    });
  }

  modificarInstitucion(institucion: Institucion): void {
    const dialogRef = this.dialog.open(FormularioInstitucionComponent, {
      width: '90%',
      maxWidth: '700px',
      disableClose: true,
    });

    dialogRef.componentInstance.institucionParaEditar = institucion;
    dialogRef.componentInstance.bloquearNombreYCarrerasEnEdicion = true;

    dialogRef.afterClosed().subscribe((resultado) => {
      if (resultado?.success) {
        this.mostrarMensaje('Institución actualizada correctamente', 'success');
        this.cargarInstituciones();
      }
    });
  }

  eliminarInstitucion(institucion: Institucion): void {
    const confirmar = confirm(`¿Eliminar la institución ${institucion.nombre}?`);
    if (!confirmar) {
      return;
    }

    this.institucionesService.eliminarInstitucion(institucion.id).subscribe({
      next: (response) => {
        if (response?.success) {
          this.mostrarMensaje('Institución eliminada correctamente', 'success');
          this.cargarInstituciones();
          return;
        }

        this.mostrarMensaje(response?.message || 'No se pudo eliminar la institución', 'error');
      },
      error: (err) => {
        const message = err?.error?.message || 'Error al eliminar la institución';
        this.mostrarMensaje(message, 'error');
      },
    });
  }

  private mostrarMensaje(mensaje: string, tipo: 'success' | 'error'): void {
    this.snackBar.open(mensaje, 'Cerrar', {
      duration: 3500,
      horizontalPosition: 'center',
      verticalPosition: 'top',
      panelClass: tipo === 'success' ? 'snackbar-success' : 'snackbar-error',
    });
  }
}
