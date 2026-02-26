import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTableModule } from '@angular/material/table';
import { MatSlideToggleModule, MatSlideToggle } from '@angular/material/slide-toggle';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatChipsModule } from '@angular/material/chips';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../../environments/environment';

interface UsuarioPendiente {
  id_usuario: number;
  email: string;
  nombre: string;
  apellido: string;
  dni: string;
  telefono: string;
  nombre_institucion: string;
  nombre_rol: string;
  fecha_registro: string;
  fecha_registro_formateada: string;
  habilitado: number;
}

@Component({
  selector: 'app-gestion-usuarios',
  imports: [
    CommonModule,
    MatTableModule,
    MatSlideToggleModule,
    MatButtonModule,
    MatIconModule,
    MatCardModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatChipsModule
  ],
  templateUrl: './gestion-usuarios.html',
  styleUrl: './gestion-usuarios.css',
})
export class GestionUsuarios implements OnInit {
  private http = inject(HttpClient);
  private snackBar = inject(MatSnackBar);
  private cdr = inject(ChangeDetectorRef);

  usuarios: UsuarioPendiente[] = [];
  displayedColumns: string[] = ['nombre', 'email', 'dni', 'institucion', 'fecha_registro', 'acciones'];
  cargando = true;

  ngOnInit(): void {
    this.cargarUsuariosPendientes();
  }

  cargarUsuariosPendientes(): void {
    this.cargando = true;
    
    this.http.get<{success: boolean, data: UsuarioPendiente[], total: number}>(
      `${environment.apiUrl}/usuarios-pendientes.php`,
      { withCredentials: true }
    ).subscribe({
      next: (response) => {
        setTimeout(() => {
          if (response.success) {
            this.usuarios = response.data;
          } else {
            this.mostrarMensaje('Error al cargar usuarios pendientes', 'error');
          }
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      },
      error: (error) => {
        console.error('Error cargando usuarios:', error);
        setTimeout(() => {
          this.mostrarMensaje('Error al cargar usuarios pendientes', 'error');
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  aprobarUsuario(usuario: UsuarioPendiente): void {
    this.http.put(
      `${environment.apiUrl}/aprobar-usuario.php`,
      {
        id_usuario: usuario.id_usuario,
        aprobar: true
      },
      { withCredentials: true }
    ).subscribe({
      next: (response: any) => {
        if (response.success) {
          setTimeout(() => {
            this.mostrarMensaje(`Usuario ${usuario.nombre} ${usuario.apellido} aprobado exitosamente`, 'success');
            this.usuarios = this.usuarios.filter(u => u.id_usuario !== usuario.id_usuario);
            this.cdr.markForCheck();
          }, 0);
        } else {
          this.mostrarMensaje(response.message || 'Error al aprobar usuario', 'error');
        }
      },
      error: (error) => {
        console.error('Error:', error);
        const mensaje = error?.error?.message || 'Error al aprobar usuario';
        this.mostrarMensaje(mensaje, 'error');
      }
    });
  }

  rechazarUsuario(usuario: UsuarioPendiente): void {
    if (!confirm(`¿Estás seguro de rechazar al usuario ${usuario.nombre} ${usuario.apellido}?`)) {
      return;
    }

    this.http.put(
      `${environment.apiUrl}/aprobar-usuario.php`,
      {
        id_usuario: usuario.id_usuario,
        aprobar: false,
        motivo: 'Rechazado por el administrador'
      },
      { withCredentials: true }
    ).subscribe({
      next: (response: any) => {
        if (response.success) {
          setTimeout(() => {
            this.mostrarMensaje(`Usuario ${usuario.nombre} ${usuario.apellido} rechazado`, 'info');
            this.usuarios = this.usuarios.filter(u => u.id_usuario !== usuario.id_usuario);
            this.cdr.markForCheck();
          }, 0);
        } else {
          this.mostrarMensaje(response.message || 'Error al rechazar usuario', 'error');
        }
      },
      error: (error) => {
        console.error('Error:', error);
        const mensaje = error?.error?.message || 'Error al rechazar usuario';
        this.mostrarMensaje(mensaje, 'error');
      }
    });
  }

  private mostrarMensaje(mensaje: string, tipo: 'success' | 'error' | 'info'): void {
    const config = {
      duration: 4000,
      horizontalPosition: 'center' as const,
      verticalPosition: 'top' as const,
      panelClass: tipo === 'success' ? 'snackbar-success' : tipo === 'error' ? 'snackbar-error' : 'snackbar-info'
    };

    this.snackBar.open(mensaje, 'Cerrar', config);
  }
}
