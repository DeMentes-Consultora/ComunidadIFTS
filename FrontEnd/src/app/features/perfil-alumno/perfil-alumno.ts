import { ChangeDetectorRef, Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../../shared/services/auth.service';
import {
  BolsaTrabajoService,
  PerfilAlumnoPostulacion,
  PerfilAlumnoUsuario,
} from '../../shared/services/bolsa-trabajo.service';
import { Carrera, Institucion } from '../../shared/models/institucion.model';
import { InstitucionesService } from '../../shared/services/instituciones.service';

@Component({
  selector: 'app-perfil-alumno',
  standalone: true,
  imports: [
    CommonModule,
    MatButtonModule,
    MatCardModule,
    MatDividerModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    FormsModule,
  ],
  templateUrl: './perfil-alumno.html',
  styleUrl: './perfil-alumno.css',
})
export class PerfilAlumno implements OnInit {
  private bolsaSvc = inject(BolsaTrabajoService);
  private authSvc = inject(AuthService);
  private institucionesSvc = inject(InstitucionesService);
  private snackBar = inject(MatSnackBar);
  private cdr = inject(ChangeDetectorRef);

  cargando = true;
  usuario: PerfilAlumnoUsuario | null = null;
  postulaciones: PerfilAlumnoPostulacion[] = [];
  eliminandoId: number | null = null;
  carrerasDisponibles: Carrera[] = [];
  aniosCursada = [1, 2, 3, 4, 5];
  editandoAcademicos = false;
  guardandoAcademicos = false;
  idCarreraSeleccionada: number | null = null;
  anioCursadaSeleccionado: number | null = null;

  ngOnInit(): void {
    this.cargarPerfil();
  }

  cargarPerfil(): void {
    this.cargando = true;
    this.bolsaSvc.obtenerPerfilAlumno().subscribe({
      next: (res) => {
        if (res.success) {
          this.usuario = res.data.usuario;
          this.postulaciones = res.data.postulaciones;
          this.cargarCarrerasDeInstitucion();
        }
        this.cargando = false;
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.snackBar.open(err?.error?.message || 'No se pudo cargar el perfil', 'Cerrar', {
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

  iniciarEdicionAcademica(): void {
    this.editandoAcademicos = true;
    this.idCarreraSeleccionada = this.usuario?.id_carrera ?? null;
    this.anioCursadaSeleccionado = this.usuario?.anio_cursada ?? null;
  }

  cancelarEdicionAcademica(): void {
    this.editandoAcademicos = false;
    this.idCarreraSeleccionada = this.usuario?.id_carrera ?? null;
    this.anioCursadaSeleccionado = this.usuario?.anio_cursada ?? null;
  }

  guardarDatosAcademicos(): void {
    if (!this.idCarreraSeleccionada || !this.anioCursadaSeleccionado || this.guardandoAcademicos) {
      return;
    }

    this.guardandoAcademicos = true;
    this.bolsaSvc.actualizarDatosAcademicos(this.idCarreraSeleccionada, this.anioCursadaSeleccionado).subscribe({
      next: (res) => {
        if (res.success && res.data) {
          this.usuario = res.data;
          this.editandoAcademicos = false;
          this.snackBar.open('Datos académicos actualizados', 'Cerrar', {
            duration: 4000,
            horizontalPosition: 'center',
            verticalPosition: 'top',
            panelClass: 'snackbar-success',
          });
        } else {
          this.snackBar.open(res.message || 'No se pudieron actualizar los datos académicos', 'Cerrar', {
            duration: 5000,
            horizontalPosition: 'center',
            verticalPosition: 'top',
            panelClass: 'snackbar-error',
          });
        }
        this.guardandoAcademicos = false;
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.snackBar.open(err?.error?.message || 'No se pudieron actualizar los datos académicos', 'Cerrar', {
          duration: 5000,
          horizontalPosition: 'center',
          verticalPosition: 'top',
          panelClass: 'snackbar-error',
        });
        this.guardandoAcademicos = false;
        this.cdr.markForCheck();
      },
    });
  }

  actualizarFotoPerfil(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) {
      return;
    }

    this.authSvc.actualizarFotoPerfil(file).subscribe({
      next: (user) => {
        if (this.usuario) {
          this.usuario = {
            ...this.usuario,
            foto_perfil_url: user.foto_perfil_url ?? null,
          };
        }
        this.snackBar.open('Foto de perfil actualizada', 'Cerrar', {
          duration: 4000,
          horizontalPosition: 'center',
          verticalPosition: 'top',
          panelClass: 'snackbar-success',
        });
        input.value = '';
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.snackBar.open(err?.message || 'No se pudo actualizar la foto', 'Cerrar', {
          duration: 5000,
          horizontalPosition: 'center',
          verticalPosition: 'top',
          panelClass: 'snackbar-error',
        });
        input.value = '';
      },
    });
  }

  quitarPostulacion(postulacion: PerfilAlumnoPostulacion): void {
    const idPostulacion = postulacion.id_postulacion;
    if (!idPostulacion || this.eliminandoId === idPostulacion) {
      return;
    }

    this.eliminandoId = idPostulacion;
    this.bolsaSvc.cancelarPostulacion(idPostulacion).subscribe({
      next: (res) => {
        if (res.success) {
          this.postulaciones = this.postulaciones.filter((item) => item.id_postulacion !== idPostulacion);
          this.snackBar.open('La oferta se quitó de tu perfil', 'Cerrar', {
            duration: 4000,
            horizontalPosition: 'center',
            verticalPosition: 'top',
            panelClass: 'snackbar-success',
          });
        } else {
          this.snackBar.open(res.message || 'No se pudo quitar la oferta', 'Cerrar', {
            duration: 5000,
            horizontalPosition: 'center',
            verticalPosition: 'top',
            panelClass: 'snackbar-error',
          });
        }
        this.eliminandoId = null;
        this.cdr.markForCheck();
      },
      error: (err) => {
        this.snackBar.open(err?.error?.message || 'No se pudo quitar la oferta', 'Cerrar', {
          duration: 5000,
          horizontalPosition: 'center',
          verticalPosition: 'top',
          panelClass: 'snackbar-error',
        });
        this.eliminandoId = null;
        this.cdr.markForCheck();
      },
    });
  }

  formatearFecha(valor: string): string {
    if (!valor) {
      return '-';
    }

    const fecha = new Date(valor);
    if (Number.isNaN(fecha.getTime())) {
      return valor;
    }

    return new Intl.DateTimeFormat('es-AR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }).format(fecha);
  }

  private cargarCarrerasDeInstitucion(): void {
    const idInstitucion = this.usuario?.id_institucion ?? 0;
    if (idInstitucion <= 0) {
      return;
    }

    this.institucionesSvc.obtenerTodas().subscribe({
      next: (instituciones: Institucion[]) => {
        const institucion = instituciones.find((item) => item.id === idInstitucion) ?? null;
        this.carrerasDisponibles = institucion?.carreras ?? [];
        this.idCarreraSeleccionada = this.usuario?.id_carrera ?? null;
        this.anioCursadaSeleccionado = this.usuario?.anio_cursada ?? null;
        this.cdr.markForCheck();
      },
      error: () => {
        this.carrerasDisponibles = [];
      },
    });
  }
}
