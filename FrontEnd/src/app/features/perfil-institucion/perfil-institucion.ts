import { ChangeDetectorRef, Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import {
  BolsaTrabajoService,
  PerfilInstitucionData,
  PerfilInstitucionPostulacion,
  PerfilInstitucionResumen,
} from '../../shared/services/bolsa-trabajo.service';

@Component({
  selector: 'app-perfil-institucion',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatButtonModule,
    MatCardModule,
    MatIconModule,
    MatInputModule,
    MatFormFieldModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
  ],
  templateUrl: './perfil-institucion.html',
  styleUrl: './perfil-institucion.css',
})
export class PerfilInstitucion implements OnInit {
  private bolsaSvc = inject(BolsaTrabajoService);
  private cdr = inject(ChangeDetectorRef);
  private snackBar = inject(MatSnackBar);

  cargando = true;
  guardando = false;
  editando = false;
  puedeEditarInstitucion = true;
  logoFile: File | null = null;
  logoPreviewUrl: string | null = null;

  institucion: PerfilInstitucionData | null = null;
  resumen: PerfilInstitucionResumen = {
    total_ofertas_publicadas: 0,
    total_postulantes: 0,
  };
  postulaciones: PerfilInstitucionPostulacion[] = [];

  form = {
    nombre: '',
    email: '',
    direccion: '',
    telefono: '',
  };

  ngOnInit(): void {
    this.cargarPerfil();
  }

  cargarPerfil(): void {
    this.cargando = true;
    this.bolsaSvc.obtenerPerfilInstitucion().subscribe({
      next: (res) => {
        if (res.success) {
          this.institucion = res.data.institucion;
          this.puedeEditarInstitucion = res.data.puede_editar_institucion !== false;
          this.resumen = res.data.resumen;
          this.postulaciones = res.data.postulaciones;
          this.sincronizarFormulario();
        }
        this.cargando = false;
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.snackBar.open(err?.error?.message || 'No se pudo cargar el perfil institucional', 'Cerrar', {
          duration: 5000,
          horizontalPosition: 'center',
          verticalPosition: 'top',
          panelClass: 'snackbar-error',
        });
        this.cargando = false;
        this.cdr.markForCheck();
      },
    });
  }

  iniciarEdicion(): void {
    if (!this.puedeEditarInstitucion) {
      return;
    }
    this.editando = true;
    this.logoFile = null;
    this.logoPreviewUrl = null;
    this.sincronizarFormulario();
  }

  cancelarEdicion(): void {
    this.editando = false;
    this.logoFile = null;
    this.logoPreviewUrl = null;
    this.sincronizarFormulario();
  }

  seleccionarLogo(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (!file) {
      return;
    }

    if (!file.type.startsWith('image/')) {
      this.snackBar.open('El archivo debe ser una imagen', 'Cerrar', {
        duration: 4000,
        horizontalPosition: 'center',
        verticalPosition: 'top',
        panelClass: 'snackbar-error',
      });
      input.value = '';
      return;
    }

    this.logoFile = file;
    this.logoPreviewUrl = URL.createObjectURL(file);
    this.cdr.markForCheck();
  }

  guardarCambios(): void {
    if (!this.institucion || this.guardando || !this.puedeEditarInstitucion || this.institucion.id <= 0) {
      return;
    }

    this.guardando = true;
    this.bolsaSvc.actualizarInstitucion({
      id: this.institucion.id,
      nombre: this.form.nombre.trim(),
      email: this.form.email.trim(),
      direccion: this.form.direccion.trim(),
      telefono: this.form.telefono.trim(),
    }, this.logoFile).subscribe({
      next: (res) => {
        if (res.success && this.institucion) {
          const logoActualizado = res.data?.logo ?? this.logoPreviewUrl ?? this.institucion.logo ?? null;
          this.institucion = {
            ...this.institucion,
            nombre: this.form.nombre.trim(),
            email: this.form.email.trim(),
            direccion: this.form.direccion.trim(),
            telefono: this.form.telefono.trim(),
            logo: logoActualizado,
          };

          if (this.logoPreviewUrl) {
            URL.revokeObjectURL(this.logoPreviewUrl);
          }
          this.logoFile = null;
          this.logoPreviewUrl = null;
          this.editando = false;
          this.snackBar.open('Datos de la institución actualizados', 'Cerrar', {
            duration: 4000,
            horizontalPosition: 'center',
            verticalPosition: 'top',
            panelClass: 'snackbar-success',
          });
        } else {
          this.snackBar.open(res.message || 'No se pudo actualizar la institución', 'Cerrar', {
            duration: 5000,
            horizontalPosition: 'center',
            verticalPosition: 'top',
            panelClass: 'snackbar-error',
          });
        }
        this.guardando = false;
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.snackBar.open(err?.error?.message || 'No se pudo actualizar la institución', 'Cerrar', {
          duration: 5000,
          horizontalPosition: 'center',
          verticalPosition: 'top',
          panelClass: 'snackbar-error',
        });
        this.guardando = false;
        this.cdr.markForCheck();
      },
    });
  }

  getNombreCompleto(item: PerfilInstitucionPostulacion): string {
    return `${item.apellido_postulante || ''} ${item.nombre_postulante || ''}`.trim() || '-';
  }

  private sincronizarFormulario(): void {
    this.form = {
      nombre: this.institucion?.nombre ?? '',
      email: this.institucion?.email ?? '',
      direccion: this.institucion?.direccion ?? '',
      telefono: this.institucion?.telefono ?? '',
    };
  }
}
