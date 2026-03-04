import { ChangeDetectionStrategy, Component, computed, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatTableModule } from '@angular/material/table';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { InstitucionesService } from '../../../shared/services/instituciones.service';
import { Institucion } from '../../../shared/models/institucion.model';
import { FormularioInstitucionComponent } from '../../../shared/components/formulario-institucion/formulario-institucion';
import { ConfirmDialogComponent } from '../../../shared/components/confirm-dialog/confirm-dialog';
import * as L from 'leaflet';

@Component({
  selector: 'app-gestion-instituciones',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatTableModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
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
  readonly filtro = signal('');
  readonly displayedColumns = [/*Encabezados de la tabla */
    'id_institucion',
    'logo_institucion',
    'nombre_institucion',
    'direccion_institucion',
    'telefono_institucion',
    'email_institucion',
    'sitio_web_institucion',
    'acciones',
  ];

  readonly institucionesOrdenadas = computed(() =>
    [...this.instituciones()].sort((a, b) => a.id - b.id)
  );

  readonly institucionesFiltradas = computed(() => {
    const termino = this.filtro().trim().toLowerCase();
    if (!termino) {
      return this.institucionesOrdenadas();
    }

    return this.institucionesOrdenadas().filter((institucion) => {
      const nombre = (institucion.nombre || '').toLowerCase();
      const direccion = (institucion.direccion || '').toLowerCase();
      const telefono = (institucion.telefono || '').toLowerCase();

      return (
        nombre.includes(termino) ||
        direccion.includes(termino) ||
        telefono.includes(termino)
      );
    });
  });

  constructor() {
    this.cargarInstituciones();
  }

  crearInstitucion(): void {
    const dialogRef = this.dialog.open(FormularioInstitucionComponent, {
      width: '90%',
      maxWidth: '700px',
      disableClose: true,
    });

    dialogRef.componentInstance.coordenadas = L.latLng(-34.6037, -58.3816);
    dialogRef.componentInstance.direccion = '';

    dialogRef.afterClosed().subscribe((resultado) => {
      if (resultado?.success) {
        this.mostrarMensaje('Institución creada correctamente', 'success');
        this.cargarInstituciones();
      }
    });
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
    const dialogRef = this.dialog.open(ConfirmDialogComponent, {
      width: '420px',
      data: {
        title: 'Eliminar institución',
        message: `¿Eliminar la institución ${institucion.nombre}?`,
        confirmText: 'Eliminar',
        cancelText: 'Cancelar',
      },
    });

    dialogRef.afterClosed().subscribe((confirmado: boolean) => {
      if (!confirmado) {
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
