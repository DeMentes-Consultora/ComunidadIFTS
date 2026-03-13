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
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatSelectModule } from '@angular/material/select';
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

interface RolDisponible {
  id_rol: number;
  nombre_rol: string;
}

interface UsuarioRegistrado {
  id_usuario: number;
  email: string;
  apellido: string;
  nombre: string;
  dni: string;
  id_rol: number;
  nombre_rol: string;
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
    MatChipsModule,
    MatFormFieldModule,
    MatSelectModule
  ],
  templateUrl: './gestion-usuarios.html',
  styleUrl: './gestion-usuarios.css',
})
export class GestionUsuarios implements OnInit {
  private http = inject(HttpClient);
  private snackBar = inject(MatSnackBar);
  private cdr = inject(ChangeDetectorRef);

  usuarios: UsuarioPendiente[] = [];
  usuariosRegistrados: UsuarioRegistrado[] = [];
  rolesDisponibles: RolDisponible[] = [];
  displayedColumns: string[] = ['nombre', 'email', 'dni', 'institucion', 'fecha_registro', 'acciones'];
  displayedColumnsRegistrados: string[] = ['usuario', 'apellido', 'nombre', 'dni', 'rol'];
  cargando = true;
  cargandoRegistrados = false;
  vistaActual: 'pendientes' | 'registrados' = 'pendientes';
  private usuariosEnProceso = new Set<number>();

  estaProcesando(idUsuario: number): boolean {
    return this.usuariosEnProceso.has(idUsuario);
  }

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
    if (this.usuariosEnProceso.has(usuario.id_usuario)) {
      return;
    }

    this.usuariosEnProceso.add(usuario.id_usuario);

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

        this.liberarUsuarioEnProceso(usuario.id_usuario);
      },
      error: (error) => {
        console.error('Error:', error);
        const mensaje = error?.error?.message || 'Error al aprobar usuario';
        this.mostrarMensaje(mensaje, 'error');
        this.liberarUsuarioEnProceso(usuario.id_usuario);
      }
    });
  }

  cambiarVista(vista: 'pendientes' | 'registrados'): void {
    this.vistaActual = vista;

    if (vista === 'registrados' && this.usuariosRegistrados.length === 0) {
      this.cargarUsuariosRegistrados();
    }
  }

  cargarUsuariosRegistrados(): void {
    this.cargandoRegistrados = true;

    this.http.get<{success: boolean, data: UsuarioRegistrado[], roles: RolDisponible[], total: number}>(
      `${environment.apiUrl}/usuarios-registrados.php`,
      { withCredentials: true }
    ).subscribe({
      next: (response) => {
        setTimeout(() => {
          if (response.success) {
            this.usuariosRegistrados = response.data ?? [];
            this.rolesDisponibles = response.roles ?? [];
          } else {
            this.mostrarMensaje('Error al cargar usuarios registrados', 'error');
          }
          this.cargandoRegistrados = false;
          this.cdr.markForCheck();
        }, 0);
      },
      error: (error) => {
        console.error('Error cargando usuarios registrados:', error);
        setTimeout(() => {
          this.mostrarMensaje('Error al cargar usuarios registrados', 'error');
          this.cargandoRegistrados = false;
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  obtenerRolesParaUsuario(usuario: UsuarioRegistrado): RolDisponible[] {
    const rolActual = this.rolesDisponibles.find((rol) => rol.id_rol === usuario.id_rol);
    const otrosRoles = this.rolesDisponibles.filter((rol) => rol.id_rol !== usuario.id_rol);
    return rolActual ? [rolActual, ...otrosRoles] : this.rolesDisponibles;
  }

  cambiarRolUsuario(usuario: UsuarioRegistrado, idRolNuevo: number): void {
    if (!idRolNuevo || idRolNuevo === usuario.id_rol) {
      return;
    }

    this.http.put<{success: boolean, message?: string, data?: {id_usuario: number, id_rol: number, nombre_rol: string | null}}>(
      `${environment.apiUrl}/cambiar-rol-usuario.php`,
      {
        id_usuario: usuario.id_usuario,
        id_rol: idRolNuevo
      },
      { withCredentials: true }
    ).subscribe({
      next: (response) => {
        if (response.success) {
          const nombreRolNuevo = response.data?.nombre_rol
            ?? this.rolesDisponibles.find((rol) => rol.id_rol === idRolNuevo)?.nombre_rol
            ?? usuario.nombre_rol;

          this.usuariosRegistrados = this.usuariosRegistrados.map((u) =>
            u.id_usuario === usuario.id_usuario
              ? { ...u, id_rol: idRolNuevo, nombre_rol: nombreRolNuevo }
              : u
          );

          this.mostrarMensaje('Rol actualizado correctamente', 'success');
          this.cdr.markForCheck();
        } else {
          this.mostrarMensaje(response.message || 'No se pudo actualizar el rol', 'error');
        }
      },
      error: (error) => {
        console.error('Error actualizando rol:', error);
        const mensaje = error?.error?.message || 'No se pudo actualizar el rol';
        this.mostrarMensaje(mensaje, 'error');
      }
    });
  }

  rechazarUsuario(usuario: UsuarioPendiente): void {
    if (this.usuariosEnProceso.has(usuario.id_usuario)) {
      return;
    }

    if (!confirm(`¿Estás seguro de rechazar al usuario ${usuario.nombre} ${usuario.apellido}?`)) {
      return;
    }

    this.usuariosEnProceso.add(usuario.id_usuario);

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

        this.liberarUsuarioEnProceso(usuario.id_usuario);
      },
      error: (error) => {
        console.error('Error:', error);
        const mensaje = error?.error?.message || 'Error al rechazar usuario';
        this.mostrarMensaje(mensaje, 'error');
        this.liberarUsuarioEnProceso(usuario.id_usuario);
      }
    });
  }

  private liberarUsuarioEnProceso(idUsuario: number): void {
    // Evita NG0100: liberar el estado en el siguiente tick de CD.
    setTimeout(() => {
      this.usuariosEnProceso.delete(idUsuario);
      this.cdr.markForCheck();
    }, 0);
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
