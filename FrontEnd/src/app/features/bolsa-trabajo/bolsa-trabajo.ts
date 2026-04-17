import { Component, OnInit, inject, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialogModule, MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { BolsaTrabajoService, OfertaLaboral } from '../../shared/services/bolsa-trabajo.service';
import { AuthService } from '../../shared/services/auth.service';

// ---- Dialog de postulación ----

@Component({
  selector: 'app-postulacion-dialog',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatButtonModule,
    MatIconModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatProgressSpinnerModule
  ],
  template: `
    <h2 mat-dialog-title>
      <mat-icon>work</mat-icon>
      Postularse a: {{ data.oferta.tituloOferta }}
    </h2>

    <mat-dialog-content>
      <div class="dialog-info">
        <p><mat-icon>school</mat-icon> <strong>{{ data.oferta.nombre_ifts }}</strong></p>
      </div>

      <div class="alumno-datos">
        <h3>Tus datos</h3>
        <p><mat-icon>person</mat-icon> {{ data.user.nombre }} {{ data.user.apellido }}</p>
        <p><mat-icon>email</mat-icon> {{ data.user.email }}</p>
        @if (data.user.telefono) {
          <p><mat-icon>phone</mat-icon> {{ data.user.telefono }}</p>
        }
      </div>

      <div class="cv-upload">
        <h3>Adjuntar CV <span class="req">*</span></h3>
        <p class="hint">Formatos aceptados: PDF, DOC, DOCX. Máximo 5 MB.</p>

        <label class="file-label" [class.has-file]="archivoSeleccionado">
          <mat-icon>upload_file</mat-icon>
          {{ archivoSeleccionado ? archivoSeleccionado.name : 'Seleccionar archivo' }}
          <input type="file" accept=".pdf,.doc,.docx" (change)="onFileChange($event)" hidden>
        </label>

        @if (errorArchivo) {
          <p class="error-text">{{ errorArchivo }}</p>
        }
      </div>
    </mat-dialog-content>

    <mat-dialog-actions align="end">
      <button mat-stroked-button [mat-dialog-close]="null" [disabled]="enviando">Cancelar</button>
      <button mat-flat-button color="primary"
        [disabled]="!archivoSeleccionado || enviando"
        (click)="postularse()">
        @if (enviando) {
          <mat-progress-spinner diameter="20" mode="indeterminate"></mat-progress-spinner>
        } @else {
          Enviar postulación
        }
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    h2[mat-dialog-title] { display: flex; align-items: center; gap: 8px; }
    .dialog-info { margin-bottom: 16px; }
    .dialog-info p { display: flex; align-items: center; gap: 8px; margin: 0; color: #555; }
    .alumno-datos { background: #f5f5f5; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; }
    .alumno-datos h3 { margin: 0 0 8px; font-size: 14px; color: #888; }
    .alumno-datos p { display: flex; align-items: center; gap: 8px; margin: 4px 0; font-size: 14px; }
    .cv-upload h3 { font-size: 15px; margin-bottom: 4px; }
    .req { color: red; }
    .hint { font-size: 12px; color: #888; margin: 0 0 10px; }
    .file-label {
      display: flex; align-items: center; gap: 8px;
      padding: 10px 16px; border: 2px dashed #bbb; border-radius: 8px;
      cursor: pointer; transition: border-color 0.2s, background 0.2s;
      font-size: 14px; color: #555;
    }
    .file-label:hover { border-color: #006633; background: #f0faf4; }
    .file-label.has-file { border-color: #006633; color: #006633; }
    .error-text { color: red; font-size: 13px; margin-top: 6px; }
    mat-dialog-actions button { display: flex; align-items: center; gap: 6px; }
  `]
})
export class PostulacionDialog {
  data: { oferta: OfertaLaboral; user: any } = inject(MAT_DIALOG_DATA);
  private dialogRef: MatDialogRef<PostulacionDialog> = inject(MatDialogRef);
  private bolsaSvc = inject(BolsaTrabajoService);
  private snackBar = inject(MatSnackBar);

  archivoSeleccionado: File | null = null;
  errorArchivo: string | null = null;
  enviando = false;

  onFileChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    this.errorArchivo = null;

    if (!input.files?.length) return;
    const file = input.files[0];

    const tiposPermitidos = ['application/pdf', 'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    if (!tiposPermitidos.includes(file.type)) {
      this.errorArchivo = 'Formato no permitido. Usá PDF, DOC o DOCX.';
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      this.errorArchivo = 'El archivo supera el límite de 5 MB.';
      return;
    }

    this.archivoSeleccionado = file;
  }

  postularse(): void {
    if (!this.archivoSeleccionado || this.enviando) return;
    this.enviando = true;

    this.bolsaSvc.postularse(this.data.oferta.id_bolsaDeTrabajo, this.archivoSeleccionado).subscribe({
      next: (res) => {
        if (res.success) {
          this.dialogRef.close(true);
        } else {
          this.snackBar.open(res.message || 'Error al postularse', 'Cerrar', {
            duration: 5000, panelClass: 'snackbar-error',
            horizontalPosition: 'center', verticalPosition: 'top'
          });
          this.enviando = false;
        }
      },
      error: (err) => {
        this.snackBar.open(err?.error?.message || 'Error al postularse', 'Cerrar', {
          duration: 5000, panelClass: 'snackbar-error',
          horizontalPosition: 'center', verticalPosition: 'top'
        });
        this.enviando = false;
      }
    });
  }
}

// ---- Componente principal ----

@Component({
  selector: 'app-bolsa-trabajo',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
    MatChipsModule,
    MatDialogModule
  ],
  templateUrl: './bolsa-trabajo.html',
  styleUrl: './bolsa-trabajo.css'
})
export class BolsaTrabajo implements OnInit {
  private bolsaSvc = inject(BolsaTrabajoService);
  private authSvc  = inject(AuthService);
  private dialog   = inject(MatDialog);
  private snackBar = inject(MatSnackBar);
  private cdr      = inject(ChangeDetectorRef);

  cargando = true;
  ofertas: OfertaLaboral[] = [];
  usuario$ = this.authSvc.currentUser$;

  ngOnInit(): void {
    this.cargarOfertas();
  }

  cargarOfertas(): void {
    this.cargando = true;
    this.bolsaSvc.obtenerOfertasPublicadas().subscribe({
      next: (res) => {
        setTimeout(() => {
          if (res.success) this.ofertas = res.data;
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      },
      error: () => {
        setTimeout(() => {
          this.snackBar.open('Error al cargar las ofertas', 'Cerrar', {
            duration: 4000, panelClass: 'snackbar-error',
            horizontalPosition: 'center', verticalPosition: 'top'
          });
          this.cargando = false;
          this.cdr.markForCheck();
        }, 0);
      }
    });
  }

  abrirPostulacion(oferta: OfertaLaboral): void {
    this.authSvc.currentUser$.subscribe(user => {
      const ref = this.dialog.open(PostulacionDialog, {
        width: '500px',
        maxWidth: '95vw',
        data: { oferta, user }
      });

      ref.afterClosed().subscribe(resultado => {
        if (resultado) {
          // Marcar la oferta como ya postulada localmente
          this.ofertas = this.ofertas.map(o =>
            o.id_bolsaDeTrabajo === oferta.id_bolsaDeTrabajo
              ? { ...o, ya_postulado: true }
              : o
          );
          this.snackBar.open('¡Postulación enviada! Revisá tu email.', 'Cerrar', {
            duration: 5000, panelClass: 'snackbar-success',
            horizontalPosition: 'center', verticalPosition: 'top'
          });
          this.cdr.markForCheck();
        }
      });
    }).unsubscribe();
  }
}
