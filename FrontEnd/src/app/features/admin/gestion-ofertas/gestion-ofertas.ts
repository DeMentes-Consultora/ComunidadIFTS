import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTableModule } from '@angular/material/table';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { BolsaTrabajoService, OfertaLaboral } from '../../../shared/services/bolsa-trabajo.service';

@Component({
  selector: 'app-gestion-ofertas',
  standalone: true,
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
    MatDialogModule
  ],
  templateUrl: './gestion-ofertas.html',
  styleUrl: './gestion-ofertas.css'
})
export class GestionOfertas implements OnInit {
  private bolsaService = inject(BolsaTrabajoService);
  private snackBar     = inject(MatSnackBar);
  private cdr          = inject(ChangeDetectorRef);

  vistaActual: 'pendientes' | 'publicadas' = 'pendientes';
  cargando = true;

  ofertasPendientes: OfertaLaboral[] = [];
  ofertasPublicadas: OfertaLaboral[] = [];

  columnsPendientes: string[] = ['institucion', 'oferta', 'fecha', 'acciones'];
  columnsPublicadas: string[] = ['institucion', 'oferta', 'postulaciones', 'toggle'];

  private enProceso = new Set<number>();

  ngOnInit(): void {
    this.cargarPendientes();
  }

  // ---- Carga de datos ----

  cargarPendientes(): void {
    this.cargando = true;
    this.bolsaService.obtenerOfertasAdmin('pendientes').subscribe({
      next: (res) => {
        setTimeout(() => {
          if (res.success) {
            this.ofertasPendientes = res.data;
          }
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      },
      error: () => {
        setTimeout(() => {
          this.mostrarMensaje('Error al cargar ofertas pendientes', 'error');
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  cargarPublicadas(): void {
    this.cargando = true;
    this.bolsaService.obtenerOfertasAdmin('publicadas').subscribe({
      next: (res) => {
        setTimeout(() => {
          if (res.success) {
            this.ofertasPublicadas = res.data;
          }
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      },
      error: () => {
        setTimeout(() => {
          this.mostrarMensaje('Error al cargar ofertas publicadas', 'error');
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  cambiarVista(vista: 'pendientes' | 'publicadas'): void {
    this.vistaActual = vista;
    if (vista === 'publicadas' && this.ofertasPublicadas.length === 0) {
      this.cargarPublicadas();
    }
  }

  // ---- Acciones ----

  aprobarOferta(oferta: OfertaLaboral): void {
    if (this.enProceso.has(oferta.id_bolsaDeTrabajo)) return;
    this.enProceso.add(oferta.id_bolsaDeTrabajo);

    this.bolsaService.gestionarOferta({
      id_bolsaDeTrabajo: oferta.id_bolsaDeTrabajo,
      accion: 'aprobar'
    }).subscribe({
      next: (res) => {
        if (res.success) {
          setTimeout(() => {
            this.mostrarMensaje(`Oferta "${oferta.tituloOferta}" publicada`, 'success');
            this.ofertasPendientes = this.ofertasPendientes.filter(o => o.id_bolsaDeTrabajo !== oferta.id_bolsaDeTrabajo);
            // Invalidar caché de publicadas para que recargue
            this.ofertasPublicadas = [];
            this.cdr.markForCheck();
          }, 0);
        } else {
          this.mostrarMensaje(res.message || 'Error al aprobar', 'error');
        }
        this.liberarProceso(oferta.id_bolsaDeTrabajo);
      },
      error: (err) => {
        this.mostrarMensaje(err?.error?.message || 'Error al aprobar la oferta', 'error');
        this.liberarProceso(oferta.id_bolsaDeTrabajo);
      }
    });
  }

  rechazarOferta(oferta: OfertaLaboral): void {
    if (this.enProceso.has(oferta.id_bolsaDeTrabajo)) return;
    if (!confirm(`¿Rechazar la oferta "${oferta.tituloOferta}"?`)) return;

    this.enProceso.add(oferta.id_bolsaDeTrabajo);

    this.bolsaService.gestionarOferta({
      id_bolsaDeTrabajo: oferta.id_bolsaDeTrabajo,
      accion: 'rechazar'
    }).subscribe({
      next: (res) => {
        if (res.success) {
          setTimeout(() => {
            this.mostrarMensaje(`Oferta "${oferta.tituloOferta}" rechazada`, 'info');
            this.ofertasPendientes = this.ofertasPendientes.filter(o => o.id_bolsaDeTrabajo !== oferta.id_bolsaDeTrabajo);
            this.cdr.markForCheck();
          }, 0);
        } else {
          this.mostrarMensaje(res.message || 'Error al rechazar', 'error');
        }
        this.liberarProceso(oferta.id_bolsaDeTrabajo);
      },
      error: (err) => {
        this.mostrarMensaje(err?.error?.message || 'Error al rechazar la oferta', 'error');
        this.liberarProceso(oferta.id_bolsaDeTrabajo);
      }
    });
  }

  togglePublicada(oferta: OfertaLaboral, habilitado: boolean): void {
    if (!habilitado) {
      // Deshabilitar oferta publicada
      this.bolsaService.gestionarOferta({
        id_bolsaDeTrabajo: oferta.id_bolsaDeTrabajo,
        accion: 'deshabilitar'
      }).subscribe({
        next: (res) => {
          if (res.success) {
            this.mostrarMensaje(`Oferta deshabilitada`, 'info');
            // Recargar publicadas
            this.ofertasPublicadas = [];
            this.cargarPublicadas();
          } else {
            this.mostrarMensaje(res.message || 'Error', 'error');
          }
        },
        error: () => this.mostrarMensaje('Error al deshabilitar la oferta', 'error')
      });
    } else {
      // Re-publicar (aprobar)
      this.aprobarOferta(oferta);
    }
  }

  estaProcesando(id: number): boolean {
    return this.enProceso.has(id);
  }

  private liberarProceso(id: number): void {
    setTimeout(() => {
      this.enProceso.delete(id);
      this.cdr.markForCheck();
    }, 0);
  }

  private mostrarMensaje(mensaje: string, tipo: 'success' | 'error' | 'info'): void {
    this.snackBar.open(mensaje, 'Cerrar', {
      duration: 4000,
      horizontalPosition: 'center',
      verticalPosition: 'top',
      panelClass: tipo === 'success' ? 'snackbar-success' : tipo === 'error' ? 'snackbar-error' : 'snackbar-info'
    });
  }
}
